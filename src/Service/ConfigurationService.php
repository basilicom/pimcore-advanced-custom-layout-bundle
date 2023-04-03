<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Service;

use Basilicom\AdvancedCustomLayoutBundle\Factory\CustomLayoutConfigFactory;
use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;

class ConfigurationService
{
    private CustomLayoutConfigFactory $customLayoutConfigFactory;
    private array $config;

    public function __construct(CustomLayoutConfigFactory $customLayoutConfigFactory, array $config)
    {
        $this->customLayoutConfigFactory = $customLayoutConfigFactory;
        $this->config = $config;
    }

    /**
     * @return CustomLayoutConfig[]
     */
    public function getCustomLayoutConfigs(): array
    {
        $customLayoutConfigs = [];
        foreach ($this->config as $class => $layouts) {
            foreach ($layouts as $layoutName => $layoutData) {
                $customLayoutConfigs[] = $this->customLayoutConfigFactory->create(
                    $class,
                    $layoutName,
                    $layoutData
                );
            }
        }

        return $customLayoutConfigs;
    }
}
