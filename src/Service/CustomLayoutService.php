<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Service;

use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;
use Exception;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\CustomLayout;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Layout;
use Pimcore\Model\DataObject\Concrete;

class CustomLayoutService
{
    private ConfigurationService $configurationService;

    public function __construct(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @throws Exception
     */
    public function importAllCustomLayouts(): void
    {
        foreach ($this->configurationService->getCustomLayoutConfigs() as $config) {
            $this->importCustomLayout($config);
        }
    }

    /**
     * @throws Exception
     */
    public function importCustomLayout(CustomLayoutConfig $layoutConfig): void
    {
        $class = $layoutConfig->getFullQualifiedClassName();
        if (!class_exists($class)) {
            throw new Exception(sprintf('Class %s does not exist', $class));
        }

        /** @var Concrete $targetClass */
        $targetClass = new $class();
        $classDefinition = $targetClass->getClass();

        $layoutDefinitions = match ($layoutConfig->getMode()) {
            CustomLayoutConfig::MODE_SHOW => $this->overwriteLayout($classDefinition, $layoutConfig),
            CustomLayoutConfig::MODE_EDIT => $this->editLayout($classDefinition, $layoutConfig),
            default => null,
        };

        $layoutId = $layoutConfig->getLayoutIdentifier();
        if ($layoutDefinitions) {
            if (!($customLayout = CustomLayout::getById($layoutId))) {
                $customLayout = new CustomLayout();
                $customLayout->setId($layoutId);
                $customLayout->setName($layoutConfig->getLabel() . ' (' . $targetClass->getClassId() . ')');
            }

            $customLayout->setClassId($targetClass->getClassId());
            $customLayout->setLayoutDefinitions($layoutDefinitions);
            $customLayout->save();
        }
    }

    private function overwriteLayout(?ClassDefinition $classDefinition, CustomLayoutConfig $layoutConfig): ?Layout
    {
        $layoutDefinitions = $classDefinition->getLayoutDefinitions();
        $fieldDefinitions = $classDefinition->getFieldDefinitions();

        foreach ($layoutConfig->getFields() as $fieldConfig) {
            foreach ($fieldDefinitions as $fieldDefinition) {
                $fieldDefinition->setInvisible(true);
                if ($fieldDefinition->getName() === $fieldConfig->getFieldId()) {
                    $fieldDefinition->setInvisible(false);
                    $this->adaptFieldDefinition($fieldDefinition, $fieldConfig);
                }
            }
        }

        return $layoutDefinitions;
    }

    private function editLayout(?ClassDefinition $classDefinition, CustomLayoutConfig $layoutConfig): ?Layout
    {
        $layoutDefinitions = $classDefinition->getLayoutDefinitions();
        $fieldDefinitions = $classDefinition->getFieldDefinitions();

        foreach ($layoutConfig->getFields() as $fieldConfig) {
            foreach ($fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->getName() === $fieldConfig->getFieldId()) {
                    $this->adaptFieldDefinition($fieldDefinition, $fieldConfig);
                    continue 2;
                }
            }
        }

        return $layoutDefinitions;
    }

    private function adaptFieldDefinition(Data $fieldDefinition, CustomLayoutConfig\FieldConfig $fieldConfig): void
    {
        if ($fieldConfig->getTitle() !== null) {
            $fieldDefinition->setTitle($fieldConfig->getTitle());
        }
        if ($fieldConfig->getIsEditable() !== null) {
            $fieldDefinition->setNoteditable(!$fieldConfig->getIsEditable());
        }
        if ($fieldConfig->getIsVisible() !== null) {
            $fieldDefinition->setInvisible(!$fieldConfig->getIsVisible());
        }
    }

    public static function getLayoutId(CustomLayoutConfig $layoutConfig, Concrete $targetClass): string
    {
        return $layoutConfig->getLayoutIdentifier() . '_' . $targetClass->getClassId();
    }
}
