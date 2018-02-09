<?php
declare(strict_types=1);

namespace Tests\Xymanek\HashidsBundle;

use Hashids\Hashids;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Xymanek\HashidsBundle\ServiceLocatorHashidsRegistry;
use PHPUnit\Framework\TestCase;
use Xymanek\HashidsBundle\Exception\InvalidDomainException;
use Xymanek\HashidsBundle\Exception\NoDefaultHashidsDomainException;

class DefaultHashidsRegistryTest extends TestCase
{
    public function testGetDefault ()
    {
        $hashids = new Hashids();
        $locator = $this->createMock(ServiceLocator::class);
        $registry = new ServiceLocatorHashidsRegistry($locator, 'default');

        $locator
            ->expects($this->once())
            ->method('get')
            ->with('default')
            ->willReturn($hashids);

        $this->assertEquals($hashids, $registry->get(null));
    }

    public function testGetDefaultException ()
    {
        $this->expectException(NoDefaultHashidsDomainException::class);

        $locator = $this->createMock(ServiceLocator::class);
        $registry = new ServiceLocatorHashidsRegistry($locator, null);

        $locator
            ->expects($this->never())
            ->method('get');

        $registry->get(null);
    }

    public function testGetCustom ()
    {
        $hashids = new Hashids();
        $locator = $this->createMock(ServiceLocator::class);
        $registry = new ServiceLocatorHashidsRegistry($locator, 'default');

        $locator
            ->expects($this->once())
            ->method('get')
            ->with('custom')
            ->willReturn($hashids);

        $this->assertEquals($hashids, $registry->get('custom'));
    }

    public function testGetCustomException ()
    {
        $this->expectException(InvalidDomainException::class);

        $locator = $this->createMock(ServiceLocator::class);
        $registry = new ServiceLocatorHashidsRegistry($locator, 'default');

        $locator
            ->expects($this->once())
            ->method('get')
            ->with('custom')
            ->willThrowException(new ServiceNotFoundException('custom'));

        $registry->get('custom');
    }

    public function testGetDefaultDomainName ()
    {
        $locator = $this->createMock(ServiceLocator::class);

        $registry = new ServiceLocatorHashidsRegistry($locator, 'default');
        $this->assertEquals('default', $registry->getDefaultDomainName());

        $registry = new ServiceLocatorHashidsRegistry($locator, 'custom');
        $this->assertEquals('custom', $registry->getDefaultDomainName());

        $registry = new ServiceLocatorHashidsRegistry($locator, null);
        $this->assertNull($registry->getDefaultDomainName());
    }
}
