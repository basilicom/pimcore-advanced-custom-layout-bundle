<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Service;

use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;
use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig\LayoutElementConfig;
use Exception;
use Pimcore\Cache\RuntimeCache;
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
            RuntimeCache::clear();
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
        $layoutDefinition = $classDefinition->getLayoutDefinitions();
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

        foreach ($layoutConfig->getLayoutElements() as $layoutElementConfig) {
            $this->adaptLayoutDefinition($layoutElementConfig, $layoutDefinition);
        }

        return $layoutDefinition;
    }

    private function editLayout(?ClassDefinition $classDefinition, CustomLayoutConfig $layoutConfig): ?Layout
    {
        $layoutDefinition = $classDefinition->getLayoutDefinitions();
        $fieldDefinitions = $classDefinition->getFieldDefinitions();

        foreach ($layoutConfig->getFields() as $fieldConfig) {
            $this->handleFieldDefinitions($fieldDefinitions, $fieldConfig);
        }

        foreach ($layoutConfig->getLayoutElements() as $layoutElementConfig) {
            $this->adaptLayoutDefinition($layoutElementConfig, $layoutDefinition);
        }

        return $layoutDefinition;
    }

    private function handleFieldDefinitions(array $fieldDefinitions, CustomLayoutConfig\FieldConfig $fieldConfig) {
        foreach ($fieldDefinitions as $fieldDefinition) {
            if($fieldDefinition instanceof Data\Localizedfields) {
                $this->handleFieldDefinitions($fieldDefinition->getFieldDefinitions(), $fieldConfig);
                continue;
            }

            if ($fieldDefinition->getName() === $fieldConfig->getFieldId()) {
                $this->adaptFieldDefinition($fieldDefinition, $fieldConfig);
            }
        }
    }


    private function adaptLayoutDefinition(LayoutElementConfig $config, Layout $layoutDefinition): Layout
    {
        if ($layoutDefinition->getName() === $config->getElementId()) {
            if ($config->getTitle() !== null) {
                $layoutDefinition->setTitle($config->getTitle());
            }
        }

        $children = $layoutDefinition->getChildren();
        if (!empty($children)) {
            $newChildren = [];
            foreach ($children as $index => $layoutChild) {
                if ($layoutChild instanceof Layout) {
                    if ($layoutChild->getName() === $config->getElementId()) {
                        if ((bool)$config->getIsVisible() === false) {
                            continue;
                        }
                    }
                    $newChildren[] = $this->adaptLayoutDefinition($config, $layoutChild);
                } else {
                    $newChildren[] = $layoutChild;
                }
            }
            $layoutDefinition->setChildren($newChildren);
        }

        return $layoutDefinition;
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
