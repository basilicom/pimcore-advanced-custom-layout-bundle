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
                    fn ($configData, $key) => $this->createFieldConfig($key, $configData),
                    $layoutData[Configuration::FIELDS],
                    array_keys($layoutData[Configuration::FIELDS])
                )
            )
        );
    }

    private function createFieldConfig(string $fieldId, array $fieldConfigData): ?CustomLayoutConfig\FieldConfig
    {
        $filteredConfigData = array_filter($fieldConfigData, fn ($data) => !is_null($data));
        if (empty($filteredConfigData)) {
            return null;
        }

        return new CustomLayoutConfig\FieldConfig(
            $fieldId,
            $fieldConfigData[Configuration::FIELD_TITLE],
            $fieldConfigData[Configuration::FIELD_EDITABLE],
            $fieldConfigData[Configuration::FIELD_VISIBLE]
        );
    }
}
