<?php
namespace Xymanek\HashidsBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class DecodeHashid
{
    /**
     * @var array
     */
    public $map = ['hashid' => 'id'];

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
}