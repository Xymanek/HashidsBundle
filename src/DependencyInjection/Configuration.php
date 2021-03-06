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
    public function getConfigTreeBuilder (): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('xymanek_hashids');

        /** @noinspection NullPointerExceptionInspection */
        $rootNode
            ->children()
                ->scalarNode('default_domain')->defaultValue('default')->end()
                ->arrayNode('domains')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
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
                ->arrayNode('listeners')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('annotations')->defaultTrue()->end()
                        ->booleanNode('request_attribute')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
