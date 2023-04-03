<?php

namespace Basilicom\AdvancedCustomLayoutBundle\DependencyInjection;

use Basilicom\AdvancedCustomLayoutBundle\Config\Configuration;
use Basilicom\AdvancedCustomLayoutBundle\Service\ConfigurationService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AdvancedCustomLayoutExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $config = $container->resolveEnvPlaceholders($config, true);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $container->getDefinition(ConfigurationService::class)->setArgument('$config', $config);
    }
}
