<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\ReaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor.
     *
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('huh');

        $rootNode
            ->children()
                ->arrayNode('reader')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('managers')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('id')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('items')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('class')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('config_element_types')
                            ->setDeprecated('Add config element types this way is deprecated. See readme how to config element types.')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('class')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('syndications')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('class')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('sort')
                                        ->defaultValue(0)
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('syndication_pdf_readers')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('class')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('delete_classes')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                    ->scalarNode('class')
                                        ->isRequired()
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('templates')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('item')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                        ->scalarNode('template')
                                            ->isRequired()
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('item_prefixes')
                                ->prototype('scalar')
                                ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
