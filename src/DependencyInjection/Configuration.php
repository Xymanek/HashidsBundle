<?php
namespace Xymanek\HashidsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const DEFAULT_ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder ()
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
                            ->scalarNode('alphabet')->defaultValue(self::DEFAULT_ALPHABET)->end()
                        ->end()
                    ->end()
                    ->defaultValue([
                        'default' => [
                            'salt' => '',
                            'min_hash_length' => 0,
                            'alphabet' => self::DEFAULT_ALPHABET,
                        ]
                    ])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
