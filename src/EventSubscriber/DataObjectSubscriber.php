<?php

namespace Basilicom\AdvancedCustomLayoutBundle\EventSubscriber;

use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;
use Basilicom\AdvancedCustomLayoutBundle\Service\ConfigurationService;
use Basilicom\AdvancedCustomLayoutBundle\Service\CustomLayoutService;
use Pimcore\Bundle\AdminBundle\Security\User\User;
use Pimcore\Event\AdminEvents;
use Pimcore\Model\DataObject\ClassDefinition\CustomLayout;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DataObjectSubscriber implements EventSubscriberInterface
{
    private ConfigurationService $configurationService;
    private TokenStorageInterface $tokenStorage;

    public function __construct(ConfigurationService $configurationService, TokenStorageInterface $tokenStorage)
    {
        $this->configurationService = $configurationService;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdminEvents::OBJECT_GET_PRE_SEND_DATA => 'onDataObjectPostLoad',
        ];
    }

    public function onDataObjectPostLoad(GenericEvent $event)
    {
        $userRoles = $this->getUserRoles();
        if (empty($userRoles)) {
            return;
        }

        $object = $event->getArgument('object');
        if (!($object instanceof Concrete)) {
            return;
        }

        $configurations = $this->configurationService->getCustomLayoutConfigs();
        foreach ($configurations as $configuration) {
            if (is_a($object, $configuration->getClassName(), true)) {
                $validators = [];

                $autoApplyForWorkflowStates = $configuration->getAutoApplyForWorkflowStates();
                if (!empty($autoApplyForWorkflowStates) && method_exists($object, 'getWorkflowState')) {
                    $validators['workflows'] = in_array($object->getWorkflowState(), $autoApplyForWorkflowStates);
                }

                $autoApplyForRoles = $configuration->getAutoApplyForRoles();
                if (!empty($autoApplyForRoles)) {
                    $validators['roles'] = !empty(array_intersect($userRoles, $autoApplyForRoles));
                }

                $eventData = (array) $event->getArgument('data');
                if (!empty($validators) && !in_array(false, array_values($validators), true)) {
                    $event->setArgument(
                        'data',
                        $this->applyCustomLayout($configuration, $object, $eventData)
                    );
                    break;
                } else {
                    $eventData['currentLayoutId'] = 0;
                    $event->setArgument('data', $eventData);
                }
            }
        }
    }

    private function getUserRoles(): ?array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof User) {
            return $user->getRoles();
        }

        return null;
    }

    private function applyCustomLayout(CustomLayoutConfig $layoutConfig, Concrete $object, array $data): array
    {
        $data['currentLayoutId'] = 0;
        $layoutId = CustomLayoutService::getLayoutId($layoutConfig, $object);
        $customLayout = CustomLayout::getById($layoutId);
        if ($customLayout instanceof CustomLayout) {
            $data['currentLayoutId'] = $customLayout->getId();
            $data['layout'] = $customLayout->getLayoutDefinitions();
            Service::enrichLayoutDefinition($data['layout'], $object);

            $data['validLayouts'] = $this->hideOtherLayoutsForNonAdmins($data['validLayouts'] ?? [], $customLayout);
        }

        return $data;
    }

    private function hideOtherLayoutsForNonAdmins(array $validLayouts, CustomLayout $customLayout): array
    {
        if (!in_array('ROLE_PIMCORE_ADMIN', $this->getUserRoles())) {
            foreach ($validLayouts as $key => $validLayout) {
                if ($validLayout['id'] !== $customLayout->getId()) {
                    unset($validLayouts[$key]);
                }
            }

            $validLayouts = array_values($validLayouts);
        }

        return $validLayouts;
    }
}
