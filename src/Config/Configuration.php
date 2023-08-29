<?php

namespace Basilicom\AdvancedCustomLayoutBundle\Config;

use Basilicom\AdvancedCustomLayoutBundle\Model\CustomLayoutConfig;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const MODE = 'mode';
    public const LABEL = 'label';
    public const AUTO_APPLY_ROLES = 'auto_apply_roles';
    public const AUTO_APPLY_WORKFLOW_STATES = 'auto_apply_workflow_states';

    public const FIELDS = 'fields';
    public const FIELD_TITLE = 'title';
    public const FIELD_EDITABLE = 'editable';
    public const FIELD_VISIBLE = 'visible';

    public const LAYOUT_ELEMENTS = 'layoutElements';
    public const LAYOUT_ELEMENT_TITLE = 'title';
    public const LAYOUT_ELEMENT_VISIBLE = 'visible';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('advanced_custom_layout');
        $treeBuilder->getRootNode()
            ->normalizeKeys(false)
            ->useAttributeAsKey('class')
            ->arrayPrototype()
                ->normalizeKeys(false)
                ->useAttributeAsKey('layout')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode(self::LABEL)->end()
                        ->enumNode(self::MODE)
                            ->values([CustomLayoutConfig::MODE_SHOW, CustomLayoutConfig::MODE_EDIT])
                            ->defaultValue(CustomLayoutConfig::MODE_SHOW)
                        ->end()
                        ->arrayNode(self::AUTO_APPLY_ROLES)
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode(self::AUTO_APPLY_WORKFLOW_STATES)
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode(self::FIELDS)
                            ->normalizeKeys(false)
                            ->useAttributeAsKey('field')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode(self::FIELD_TITLE)->defaultNull()->end()
                                    ->booleanNode(self::FIELD_EDITABLE)->defaultNull()->end()
                                    ->booleanNode(self::FIELD_VISIBLE)->defaultNull()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(self::LAYOUT_ELEMENTS)
                            ->normalizeKeys(false)
                            ->useAttributeAsKey('layoutElement')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode(self::LAYOUT_ELEMENT_TITLE)->defaultNull()->end()
                                    ->booleanNode(self::LAYOUT_ELEMENT_VISIBLE)->defaultNull()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
