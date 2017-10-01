<?php
namespace Hedgebot\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Hedgebot\StorageBundle\Service\StorageService;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hedgebot_core');
        
        $rootNode
            ->children()
                ->arrayNode('api')
                    ->children()
                        ->scalarNode('uri')->end()
                        ->scalarNode('token')->defaultValue('')->end()
                    ->end()
                ->end()
                ->scalarNode('config_path')->defaultValue('%kernel.root_dir%/config/hedgebot.yml')->end()
                ->scalarNode('layout_path')->defaultValue('@HedgebotCoreBundle/Resources/config/layouts.yml')->end()
            ->end();

        return $treeBuilder;
    }
}
