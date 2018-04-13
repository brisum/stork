<?php

namespace Brisum\Symfony\Cms\Bundle\SymfonyCmsPageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('symfony_cms_page');

        $rootNode
            ->children()
                ->arrayNode('templates')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')
                    ->end()
                ->end() // templates
                ->arrayNode('statuses')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')
                    ->end()
                ->end() // statuses
            ->end()
        ;

        return $treeBuilder;
    }
}