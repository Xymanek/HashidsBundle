<?php
declare(strict_types=1);

namespace Tests\Xymanek\HashidsBundle\DependencyInjection;

use Hashids\HashidsInterface;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Reference;
use Xymanek\HashidsBundle\DependencyInjection\Configuration;
use Xymanek\HashidsBundle\DependencyInjection\XymanekHashidsExtension;
use Xymanek\HashidsBundle\HashidsRegistry;

class XymanekHashidsExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions ()
    {
        return [new XymanekHashidsExtension()];
    }

    public function testDisableAnnotationsListener ()
    {
        $this->load(['listeners' => ['annotations' => false]]);

        $this->assertContainerBuilderNotHasService('xymanek_hashids.event_listener.annotations');
    }

    public function testExceptionOnNotConfiguredDefaultDomain ()
    {
        $this->expectExceptionMessage('The specified default domain is not configured');
        $this->load(['default_domain' => 'custom']);
    }

    public function testDefaultConfiguration ()
    {
        $this->load();

        $this->assertContainerBuilderHasService('xymanek_hashids.event_listener.annotations');
        $this->assertContainerBuilderHasService('xymanek_hashids.twig_extension');

        $this->assertContainerBuilderHasAlias(HashidsRegistry::class, 'xymanek_hashids.registry');
        $this->assertContainerBuilderHasAlias('hashids_registry', 'xymanek_hashids.registry');
        $this->assertContainerBuilderHasAlias(HashidsInterface::class, 'hashids.default');
        $this->assertContainerBuilderHasAlias('hashids', 'hashids.default');

        $this->assertContainerBuilderHasServiceDefinitionWithParent('hashids.default', 'xymanek_hashids.abstract');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('hashids.default', 0, '');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('hashids.default', 1, 0);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'hashids.default', 2, Configuration::DEFAULT_ALPHABET
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('xymanek_hashids.registry', 1, 'default');
        $reference = $this->container->getDefinition('xymanek_hashids.registry')->getArgument(0);

        $this->assertInstanceOf(Reference::class, $reference);
        $this->assertStringStartsWith('service_locator', (string) $reference);
    }

    public function testDisableDefaultAlias ()
    {
        $this->load(['default_domain' => null]);

        $this->assertContainerBuilderHasService('xymanek_hashids.event_listener.annotations');
        $this->assertContainerBuilderHasService('xymanek_hashids.twig_extension');

        $this->assertContainerBuilderHasAlias(HashidsRegistry::class, 'xymanek_hashids.registry');
        $this->assertContainerBuilderHasAlias('hashids_registry', 'xymanek_hashids.registry');

        $this->assertContainerBuilderHasServiceDefinitionWithParent('hashids.default', 'xymanek_hashids.abstract');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('hashids.default', 0, '');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('hashids.default', 1, 0);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'hashids.default', 2, Configuration::DEFAULT_ALPHABET
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('xymanek_hashids.registry', 1, null);
        $reference = $this->container->getDefinition('xymanek_hashids.registry')->getArgument(0);

        $this->assertInstanceOf(Reference::class, $reference);
        $this->assertStringStartsWith('service_locator', (string) $reference);
    }

    public function testCustomDomain ()
    {
        $this->load([
            'default_domain' => null,
            'domains' => [
                'custom' => [
                    'salt' => 'abc',
                    'min_hash_length' => 20,
                    'alphabet' => 'abcdefghijklmnopqrstuvwxyz',
                ]
            ]
        ]);

        $this->assertContainerBuilderHasService('xymanek_hashids.event_listener.annotations');
        $this->assertContainerBuilderHasService('xymanek_hashids.twig_extension');

        $this->assertContainerBuilderHasAlias(HashidsRegistry::class, 'xymanek_hashids.registry');
        $this->assertContainerBuilderHasAlias('hashids_registry', 'xymanek_hashids.registry');

        $this->assertContainerBuilderHasServiceDefinitionWithParent('hashids.custom', 'xymanek_hashids.abstract');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('hashids.custom', 0, 'abc');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('hashids.custom', 1, 20);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'hashids.custom', 2, 'abcdefghijklmnopqrstuvwxyz'
        );

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('xymanek_hashids.registry', 1, null);
        $reference = $this->container->getDefinition('xymanek_hashids.registry')->getArgument(0);

        $this->assertInstanceOf(Reference::class, $reference);
        $this->assertStringStartsWith('service_locator', (string) $reference);
    }
}
