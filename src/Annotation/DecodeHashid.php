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
    public $encodedKey = 'hashid';

    /**
     * @var string
     */
    public $decodedKey = 'id';

    /**
     * @var string
     */
    public $domain = null;

    /**
     * @Enum({"EXCEPTION", "HTTP_NOT_FOUND", "CONTROLLER_METHOD", "SET_NULL"})
     */
    public $behaviourInvalid = 'HTTP_NOT_FOUND';

    /**
     * The method to call on controller if $behaviourInvalid === CONTROLLER_METHOD
     *
     * The method will receive two arguments:
     *
     * * The request object
     * * The attribute key that failed decoding
     *
     * @var string
     */
    public $method = null;

    /**
     * @Enum({"ALWAYS_FIRST", "ARRAY_IF_MULTIPLE", "ALWAYS_ARRAY"})
     */
    public $behaviourArray = 'ARRAY_IF_MULTIPLE';

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
}