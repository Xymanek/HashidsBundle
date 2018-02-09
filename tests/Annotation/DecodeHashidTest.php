<?php
declare(strict_types=1);

namespace Tests\Xymanek\HashidsBundle\Annotation;

use InvalidArgumentException;
use Xymanek\HashidsBundle\Annotation\DecodeHashid;
use PHPUnit\Framework\TestCase;

class DecodeHashidTest extends TestCase
{
    public function testDefaults ()
    {
        // The annotation should be usable without any arguments
        $annotation = new DecodeHashid([]);

        $this->assertEquals('hashid', $annotation->getEncodedKey());
        $this->assertEquals('id', $annotation->getDecodedKey());
        $this->assertEquals(null, $annotation->getDomain());
        $this->assertEquals('HTTP_NOT_FOUND', $annotation->getBehaviourInvalid());
        $this->assertEquals(null, $annotation->getMethod());
        $this->assertEquals('ARRAY_IF_MULTIPLE', $annotation->getBehaviourArray());
    }

    public function testPropertiesFilled ()
    {
        $annotation = new DecodeHashid(['method' => 'methodA']);

        $this->assertEquals('methodA', $annotation->getMethod());
    }

    public function testExceptionOnInvalid ()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Annotation %s does not have %s property',
            DecodeHashid::class, 'property'
        ));

        new DecodeHashid(['property' => 'abc']);
    }

    public function testExceptionIfControllerAndNoMethod ()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"CONTROLLER_METHOD" option requires method property to be set');

        new DecodeHashid(['behaviourInvalid' => 'CONTROLLER_METHOD']);
    }
}
