<?php
namespace Xymanek\HashidsBundle\DependencyInjection;

use Hashids\HashidsInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
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
            throw new \InvalidArgumentException('The specified default domain is not configured');
        }

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if (!$config['listeners']['annotations']) {
            $container->removeDefinition('xymanek_hashids.event_listener.annotations');
        }

        $map = [];

        foreach ($config['domains'] as $domain => $options) {
            $service = 'hashids.' . $domain;
            $map[$domain] = new Reference($service);

            $container
                ->setDefinition($service, new ChildDefinition('xymanek_hashids.abstract'))
                ->replaceArgument(0, $options['salt'])
                ->replaceArgument(1, $options['min_hash_length'])
                ->replaceArgument(2, $options['alphabet']);
        }

        if ($config['default_domain'] !== null) {
            $container->setAlias(HashidsInterface::class, 'hashids.' . $config['default_domain']);
            $container->setAlias('hashids', 'hashids.' . $config['default_domain']);
        }

        $container->findDefinition('xymanek_hashids.registry')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $map))
            ->replaceArgument(1, $config['default_domain']);
    }
}
