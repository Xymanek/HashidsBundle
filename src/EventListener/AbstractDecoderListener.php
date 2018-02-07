<?php
declare(strict_types=1);

namespace Xymanek\HashidsBundle\EventListener;

use LogicException;
use stdClass;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xymanek\HashidsBundle\Exception\DecodingFailureException;
use Xymanek\HashidsBundle\Exception\InvalidDomainException;
use Xymanek\HashidsBundle\HashidsRegistry;

abstract class AbstractDecoderListener
{
    const INVALID_BEHAVIOUR_EXCEPTION = 'exception';
    const INVALID_BEHAVIOUR_HTTP_NOT_FOUND = 'http_not_found';
    const INVALID_BEHAVIOUR_SET_NULL = 'set_null';

    const ARRAY_BEHAVIOUR_ALWAYS_FIRST = 'always_first';
    const ARRAY_BEHAVIOUR_ARRAY_IF_MULTIPLE = 'array_if_multiple';
    const ARRAY_BEHAVIOUR_ALWAYS_ARRAY = 'always_array';

    /**
     * @var HashidsRegistry
     */
    protected $registry;

    public function __construct (HashidsRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Request     $request
     * @param string|null $domain
     * @param string      $encodedKey
     * @param string      $decodedKey
     * @param string      $invalidBehaviour One of INVALID_BEHAVIOUR_* constants
     * @param string      $arrayBehaviour One of ARRAY_BEHAVIOUR_* constants
     *
     * @throws DecodingFailureException
     * @throws NotFoundHttpException
     */
    protected function decodeFromRequest (
        Request $request,
        string $domain = null,
        string $encodedKey,
        string $decodedKey,
        string $invalidBehaviour,
        string $arrayBehaviour
    ) {
        try {
            $hashids = $this->registry->get($domain);
        } catch (ServiceNotFoundException $e) {
            // TODO: Move this to registry
            throw new InvalidDomainException("Hashids domain $domain does not exist", 0, $e);
        }

        $testMissing = new stdClass();
        $encoded = $request->get($encodedKey, $testMissing);

        if ($encoded !== $testMissing) {
            $decoded = $hashids->decode($encoded);

            if (empty($decoded)) {
                $decoded = $this->failDecoding(
                    $invalidBehaviour,
                    "Failed to decode hashid from $encodedKey request attribute"
                );
            }
        } else {
            $decoded = $this->failDecoding($invalidBehaviour, "Request attribute $encodedKey does not exist");
        }

        switch ($arrayBehaviour) {
            case self::ARRAY_BEHAVIOUR_ALWAYS_FIRST:
                $decoded = $decoded[0];
            break;

            case self::ARRAY_BEHAVIOUR_ARRAY_IF_MULTIPLE:
                if (count($decoded) === 1) {
                    $decoded = $decoded[0];
                }
            break;

            case self::ARRAY_BEHAVIOUR_ALWAYS_ARRAY:
                //$decoded = $decoded;
            break;

            default:
                throw new LogicException('This code should not be reached');
        }

        $request->attributes->set($decodedKey, $decoded);
    }

    /**
     * Depending on $invalidBehaviour this method will either throw and exception or return a substitute
     *
     * @param string $invalidBehaviour
     * @param string $reason
     *
     * @return array
     *
     * @throws DecodingFailureException
     * @throws NotFoundHttpException
     */
    protected function failDecoding (string $invalidBehaviour, string $reason = ''): array
    {
        switch ($invalidBehaviour) {
            case self::INVALID_BEHAVIOUR_EXCEPTION:
                throw new DecodingFailureException($reason);
            break;

            case self::INVALID_BEHAVIOUR_HTTP_NOT_FOUND:
                throw new NotFoundHttpException($reason);
            break;

            case self::INVALID_BEHAVIOUR_SET_NULL:
                return [null];
            break;
        }

        throw new LogicException('This code should not be reached');
    }
}