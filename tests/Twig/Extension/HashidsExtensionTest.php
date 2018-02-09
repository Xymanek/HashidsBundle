<?php
declare(strict_types=1);

namespace Tests\Xymanek\HashidsBundle\Twig\Extension;

use Hashids\Hashids;
use Xymanek\HashidsBundle\HashidsRegistry;
use Xymanek\HashidsBundle\Twig\Extension\HashidsExtension;
use PHPUnit\Framework\TestCase;

class HashidsExtensionTest extends TestCase
{
    public function testEncode ()
    {
        $registry = $this->createMock(HashidsRegistry::class);
        $hashids = $this->createMock(Hashids::class);
        $extension = new HashidsExtension($registry);

        $registry
            ->expects($this->once())
            ->method('get')
            ->with('custom')
            ->willReturn($hashids);

        $hashids
            ->expects($this->once())
            ->method('encode')
            ->with(10)
            ->willReturn('abc');

        $this->assertEquals('abc', $extension->encode(10, 'custom'));
    }

    public function testDecode ()
    {
        $registry = $this->createMock(HashidsRegistry::class);
        $hashids = $this->createMock(Hashids::class);
        $extension = new HashidsExtension($registry);

        $registry
            ->expects($this->once())
            ->method('get')
            ->with('custom')
            ->willReturn($hashids);

        $hashids
            ->expects($this->once())
            ->method('decode')
            ->with('abc')
            ->willReturn([10]);

        $this->assertEquals([10], $extension->decode('abc', 'custom'));
    }
}
