<?php
namespace Xymanek\HashidsBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Xymanek\HashidsBundle\Annotation\DecodeHashid;
use Xymanek\HashidsBundle\Exception\DecodingFailureException;
use Xymanek\HashidsBundle\HashidsDomainsRegistry;

class DecodeHashidAnnotationListener
{
    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * @var HashidsDomainsRegistry
     */
    protected $registry;

    public function __construct (HashidsDomainsRegistry $registry, Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        $this->registry = $registry;
    }

    public function onKernelController (FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        $controllerMethodSupported = true;

        if (is_array($controller)) {
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && is_callable($controller, '__invoke')) {
            $reflection = new \ReflectionMethod($controller, '__invoke');
        } else {
            $reflection = new \ReflectionFunction($controller);
            $controllerMethodSupported = false;
        }

        $annotations = $this->annotationReader->getMethodAnnotations($reflection);

        foreach ($annotations as $annotation) {
            if (!$annotation instanceof DecodeHashid) {
                continue;
            }

            if ($annotation->behaviourInvalid === 'CONTROLLER_METHOD') {
                if (!$controllerMethodSupported) {
                    throw new \InvalidArgumentException(
                        '"CONTROLLER_METHOD" option is not supported when controller is not an object'
                    );
                }

                if ($annotation->method == null) {
                    throw new \InvalidArgumentException(
                        '"CONTROLLER_METHOD" option requires method property to be set'
                    );
                }
            }

            $domain = $annotation->domain;

            try {
                $hashids = $this->registry->get($domain);
            } catch (ServiceNotFoundException $e) {
                throw new DecodingFailureException("Hashids domain $domain does not exist", 0, $e);
            }

            foreach ($annotation->map as $encodedKey => $decodedKey) {
                if (!$request->attributes->has($encodedKey)) {
                    throw new DecodingFailureException("Request attribute $encodedKey does not exist");
                }

                $encoded = $request->attributes->get($encodedKey);
                $decoded = $hashids->decode($encoded);

                if ($decoded === '' || count($decoded) === 0) {
                    switch ($annotation->behaviourInvalid) {
                        case 'EXCEPTION':
                            throw new DecodingFailureException('Invalid encoded version');
                        break;

                        case 'HTTP_NOT_FOUND':
                            throw new NotFoundHttpException("Failed to decode hashid from $encodedKey request attribute");
                        break;

                        case 'CONTROLLER_METHOD':
                            if (is_object($controller)) {
                                $controller = [$controller];
                            }

                            call_user_func([$controller[0], $annotation->method], $request, $encodedKey);
                        break;

                        case 'SET_NULL':
                            $decoded = [null];
                        break;
                    }
                }

                switch ($annotation->behaviourArray) {
                    case 'ALWAYS_FIRST':
                        $decoded = $decoded[0];
                    break;

                    case 'ARRAY_IF_MULTIPLE':
                        if (count($decoded) === 1) {
                            $decoded = $decoded[0];
                        }
                    break;

                    case 'ALWAYS_ARRAY':
                        //$decoded = $decoded;
                    break;
                }

                $request->attributes->set($decodedKey, $decoded);
            }

        }
    }
}