<?php
namespace Xymanek\HashidsBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Xymanek\HashidsBundle\Annotation\DecodeHashid;
use Xymanek\HashidsBundle\Exception\DecodingFailureException;
use Xymanek\HashidsBundle\HashidsRegistry;

class DecodeHashidAnnotationListener extends AbstractDecoderListener
{
    /**
     * @var Reader
     */
    protected $annotationReader;

    public function __construct (HashidsRegistry $registry, Reader $annotationReader)
    {
        parent::__construct($registry);
        $this->annotationReader = $annotationReader;
    }

    public function onKernelController (FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        $controllerMethodSupported = true;

        if (is_array($controller)) {
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && is_callable($controller)) {
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
                    // TODO: Move this check to annotation itself
                    throw new \InvalidArgumentException(
                        '"CONTROLLER_METHOD" option requires method property to be set'
                    );
                }
            }

            foreach ($annotation->map as $encodedKey => $decodedKey) {
                $behaviourInvalid = $annotation->behaviourInvalid;

                if ($behaviourInvalid === 'CONTROLLER_METHOD') {
                    $behaviourInvalid = self::INVALID_BEHAVIOUR_EXCEPTION;
                }

                try {
                    $this->decodeFromRequest(
                        $request,
                        $annotation->domain,
                        $encodedKey,
                        $decodedKey,
                        $behaviourInvalid,
                        $annotation->behaviourArray
                    );
                } catch (DecodingFailureException $e) {
                    if ($annotation->behaviourInvalid === 'CONTROLLER_METHOD') {
                        if (is_object($controller)) {
                            $controller = [$controller];
                        }

                        call_user_func([$controller[0], $annotation->method], $request, $encodedKey);
                    } else {
                        throw $e;
                    }
                }
            }
        }
    }
}