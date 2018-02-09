<?php
namespace Xymanek\HashidsBundle\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class DecodeHashid
{
    /**
     * @var string
     */
    private $encodedKey = 'hashid';

    /**
     * @var string
     */
    private $decodedKey = 'id';

    /**
     * @var string|null
     */
    private $domain;

    /**
     * @var string
     *
     * @Enum({"EXCEPTION", "HTTP_NOT_FOUND", "CONTROLLER_METHOD", "SET_NULL"})
     */
    private $behaviourInvalid = 'HTTP_NOT_FOUND';

    /**
     * The method to call on controller if $behaviourInvalid === CONTROLLER_METHOD
     *
     * The method will receive two arguments:
     *
     * * The request object
     * * The attribute key that failed decoding
     *
     * @var string|null
     */
    private $method;

    /**
     * @var string
     *
     * @Enum({"ALWAYS_FIRST", "ARRAY_IF_MULTIPLE", "ALWAYS_ARRAY"})
     */
    private $behaviourArray = 'ARRAY_IF_MULTIPLE';

    public function __construct (array $values)
    {
        foreach ($values as $key => $value) {
            if (!property_exists(self::class, $key)) {
                throw new InvalidArgumentException(sprintf(
                    'Annotation %s does not have %s property',
                    self::class, $key
                ));
            }

            $this->{$key} = $value;
        }

        if ($this->behaviourInvalid === 'CONTROLLER_METHOD' && $this->method === null) {
            throw new InvalidArgumentException('"CONTROLLER_METHOD" option requires method property to be set');
        }
    }

    public function getEncodedKey (): string
    {
        return $this->encodedKey;
    }

    public function getDecodedKey (): string
    {
        return $this->decodedKey;
    }

    /**
     * @return null|string
     */
    public function getDomain ()
    {
        return $this->domain;
    }

    public function getBehaviourInvalid (): string
    {
        return $this->behaviourInvalid;
    }

    /**
     * @return null|string
     */
    public function getMethod ()
    {
        return $this->method;
    }

    public function getBehaviourArray (): string
    {
        return $this->behaviourArray;
    }
}