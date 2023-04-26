<?php

namespace Tests\Unit;

use Basilicom\AdvancedCustomLayoutBundle\EventSubscriber\ClassDefinitionSubscriber;
use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;
use Basilicom\AdvancedCustomLayoutBundle\Service\ConfigurationService;
use Basilicom\AdvancedCustomLayoutBundle\Service\CustomLayoutService;
use Pimcore\Event\Model\DataObject\ClassDefinitionEvent;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject\ClassDefinition;

class ClassDefinitionSubscriberTest extends TestCase
{
    public function testOnClassDefinitionPostUpdate()
    {
        $config1 = new CustomLayoutConfig(
            '\Pimcore\Model\DataObject\TestObject1',
            'layout1',
            'Label 1',
            '',
            [],
            [],
            []
        );

        $config2 = new CustomLayoutConfig(
            '\Pimcore\Model\DataObject\TestObject2',
            'layout2',
            'Label 2',
            '',
            [],
            [],
            []
        );

        $configurationService = $this->createMock(ConfigurationService::class);
        $configurationService->expects($this->once())
            ->method('getCustomLayoutConfigs')
            ->willReturn([$config1, $config2]);

        $customLayoutService = $this->createMock(CustomLayoutService::class);
        $customLayoutService->expects($this->once())
            ->method('importCustomLayout')
            ->with($config2);

        $classDefinition = new ClassDefinition();
        $classDefinition->setName('TestObject2');

        $classDefinitionEvent = $this->createMock(ClassDefinitionEvent::class);
        $classDefinitionEvent->expects($this->once())
            ->method('getClassDefinition')
            ->willReturn($classDefinition);

        $subscriber = new ClassDefinitionSubscriber($configurationService, $customLayoutService);
        $subscriber->onClassDefinitionPostUpdate($classDefinitionEvent);
    }
}

class TestObject1
{
}

class TestObject2
{
}

class_alias('Tests\Unit\TestObject1', 'Pimcore\Model\DataObject\TestObject1');
class_alias('Tests\Unit\TestObject2', 'Pimcore\Model\DataObject\TestObject2');
