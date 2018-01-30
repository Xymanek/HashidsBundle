<?php
namespace Xymanek\HashidsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('xymanek_hashids');

        $rootNode
            ->children()
                ->scalarNode('default_domain')->defaultValue('default')->end()
                ->arrayNode('domains')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('salt')->defaultValue('')->end()
                            ->scalarNode('min_hash_length')->defaultValue(0)->end()
                            ->scalarNode('alphabet')->defaultValue('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')->end()
                        ->end()
                    ->end()
                    ->defaultValue(['default' => ['salt' => '', 'min_hash_length' => 0, 'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890']])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
