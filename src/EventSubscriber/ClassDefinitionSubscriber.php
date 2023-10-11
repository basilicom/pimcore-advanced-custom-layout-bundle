<?php

namespace Basilicom\AdvancedCustomLayoutBundle\EventSubscriber;

use Basilicom\AdvancedCustomLayoutBundle\Service\ConfigurationService;
use Basilicom\AdvancedCustomLayoutBundle\Service\CustomLayoutService;
use Pimcore\Event\DataObjectClassDefinitionEvents;
use Pimcore\Event\Model\DataObject\ClassDefinitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClassDefinitionSubscriber implements EventSubscriberInterface
{
    private ConfigurationService $configurationService;
    private CustomLayoutService $customLayoutService;

    public function __construct(ConfigurationService $configurationService, CustomLayoutService $customLayoutService)
    {
        $this->configurationService = $configurationService;
        $this->customLayoutService = $customLayoutService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectClassDefinitionEvents::POST_UPDATE => 'onClassDefinitionPostUpdate',
        ];
    }

    public function onClassDefinitionPostUpdate(ClassDefinitionEvent $event): void
    {
        $classDefinition = $event->getClassDefinition();

        foreach ($this->configurationService->getCustomLayoutConfigs() as $config) {
            $classDefinitionClass = '\\Pimcore\\Model\\DataObject\\' . ucfirst($classDefinition->getName());
            if (is_a($classDefinitionClass, $config->getClassName(), true)) {
                $this->customLayoutService->importCustomLayout($config);
            }
        }
    }
}
