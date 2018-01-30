<?php
namespace Xymanek\HashidsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class XymanekHashidsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load (array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if ($config['default_domain'] !== null && !isset($config['domains'][$config['default_domain']])) {
            throw new \InvalidArgumentException('The specified default domain does not exist');
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $map = [];

        foreach ($config['domains'] as $domain => $options) {
            $service = 'hashids.' . $domain;
            $map[$domain] = new Reference($service);

            $container
                ->setDefinition($service, new ChildDefinition('xymanek_hashids.abstract'))
                ->setArguments([$options['salt'], $options['min_hash_length'], $options['alphabet']]);
        }

        $container->setParameter('xymanek_hashids.default_domain', $config['default_domain']);

        if ($config['default_domain'] !== null) {
            $container->setAlias('hashids', 'hashids.' . $config['default_domain']);
        }

        $container->register('xymanek_hashids.resolver', ServiceLocator::class)
            ->setPublic(false)
            ->addTag('container.service_locator')
            ->setArgument(0, $map);
    }
}
