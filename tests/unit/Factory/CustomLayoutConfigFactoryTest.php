<?php

namespace Tests\Unit\Factory;

use Basilicom\AdvancedCustomLayoutBundle\Config\Configuration;
use Basilicom\AdvancedCustomLayoutBundle\Factory\CustomLayoutConfigFactory;
use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;
use PHPUnit\Framework\TestCase;

class CustomLayoutConfigFactoryTest extends TestCase
{
    public function testCreate()
    {
        $factory = new CustomLayoutConfigFactory();

        $className = 'MyClass';
        $layoutName = 'MyLayout';
        $layoutData = [
            Configuration::LABEL                      => 'My Label',
            Configuration::MODE                       => 'My Mode',
            Configuration::AUTO_APPLY_ROLES           => ['ROLE1', 'ROLE2'],
            Configuration::AUTO_APPLY_WORKFLOW_STATES => ['STATE1', 'STATE2'],
            Configuration::FIELDS                     => [
                'field1' => [
                    Configuration::FIELD_TITLE    => 'Field 1 Title',
                    Configuration::FIELD_EDITABLE => true,
                    Configuration::FIELD_VISIBLE  => false,
                ],
                'field2' => [
                    Configuration::FIELD_TITLE    => 'Field 2 Title',
                    Configuration::FIELD_EDITABLE => false,
                    Configuration::FIELD_VISIBLE  => true,
                ],
            ],
        ];

        $expectedConfig = new CustomLayoutConfig(
            $className,
            $layoutName,
            'My Label',
            'My Mode',
            ['ROLE1', 'ROLE2'],
            ['STATE1', 'STATE2'],
            [
                new CustomLayoutConfig\FieldConfig('field1', 'Field 1 Title', true, false),
                new CustomLayoutConfig\FieldConfig('field2', 'Field 2 Title', false, true),
            ]
        );

        $actualConfig = $factory->create($className, $layoutName, $layoutData);

        $this->assertEquals($expectedConfig, $actualConfig);
    }
}
