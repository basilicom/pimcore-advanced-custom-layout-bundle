<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Factory;

use Basilicom\AdvancedCustomLayoutBundle\Config\Configuration;
use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;

class CustomLayoutConfigFactory
{
    public function create(string $className, string $layoutName, array $layoutData): CustomLayoutConfig
    {
        return new CustomLayoutConfig(
            $className,
            $layoutName,
            (string) $layoutData[Configuration::LABEL],
            (string) $layoutData[Configuration::MODE],
            (array) $layoutData[Configuration::AUTO_APPLY_ROLES],
            (array) $layoutData[Configuration::AUTO_APPLY_WORKFLOW_STATES],
            array_filter(
                array_map(
                    fn($configData, $key) => $this->createFieldConfig($key, $configData),
                    $layoutData[Configuration::FIELDS],
                    array_keys($layoutData[Configuration::FIELDS])
                )
            ),
            array_filter(
                array_map(
                    fn($configData, $key) => $this->createLayoutElementConfig($key, $configData),
                    $layoutData[Configuration::LAYOUT_ELEMENTS],
                    array_keys($layoutData[Configuration::LAYOUT_ELEMENTS])
                )
            )
        );
    }

    private function createFieldConfig(string $fieldId, array $configData): ?CustomLayoutConfig\FieldConfig
    {
        $filteredConfigData = array_filter($configData, fn($data) => !is_null($data));
        if (empty($filteredConfigData)) {
            return null;
        }

        return new CustomLayoutConfig\FieldConfig(
            $fieldId,
            $configData[Configuration::FIELD_TITLE],
            $configData[Configuration::FIELD_EDITABLE],
            $configData[Configuration::FIELD_VISIBLE]
        );
    }

    private function createLayoutElementConfig(string $elementId, array $configData): ?CustomLayoutConfig\LayoutElementConfig
    {
        $filteredConfigData = array_filter($configData, fn($data) => !is_null($data));
        if (empty($filteredConfigData)) {
            return null;
        }

        return new CustomLayoutConfig\LayoutElementConfig(
            $elementId,
            $configData[Configuration::LAYOUT_ELEMENT_TITLE],
            $configData[Configuration::LAYOUT_ELEMENT_VISIBLE]
        );
    }
}
