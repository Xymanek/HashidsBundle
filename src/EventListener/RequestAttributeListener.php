<?php
declare(strict_types=1);

namespace Xymanek\HashidsBundle\EventListener;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xymanek\HashidsBundle\HashidsRegistry;

class RequestAttributeListener
{
    const REQUEST_ATTRIBUTE = 'decode_hashids';

    /**
     * @var HashidsRegistry
     */
    private $registry;

    /**
     * @var string
     */
    private $defaultDomain;

    public function __construct (HashidsRegistry $registry, string $defaultDomain = null)
    {
        $this->registry = $registry;
        $this->defaultDomain = $defaultDomain;
    }

    public function onKernelRequest (GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has(self::REQUEST_ATTRIBUTE)) {
            return;
        }

        $userOptions = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        if (!is_array($userOptions)) {
            throw new InvalidArgumentException(sprintf(
                'Request attribute %s must be an array, %s given',
                self::REQUEST_ATTRIBUTE, is_object($userOptions) ? get_class($userOptions) : gettype($userOptions)
            ));
        }

        $options = [];
        $resolver = $this->makeResolver();

        foreach ($userOptions as $parameter => $config) {
            if (is_string($config)) {
                $config = ['target' => $config];
            }

            $options[$parameter] = $resolver->resolve($config);
        }

        foreach ($options as $parameter => $config) {
            // TODO
        }
    }

    protected function makeResolver (): OptionsResolver
    {
        $resolver = new OptionsResolver();

        $resolver
            ->setDefaults([
                'behaviour_invalid' => 'http_not_found',
                'behaviour_array' => 'array_if_multiple',
            ])
            ->setRequired('target')
            ->setAllowedTypes('domain', 'string')
            ->setAllowedTypes('target', 'string')
            ->setAllowedValues('behaviour_invalid', [
                'exception',
                'http_not_found',
                'set_null',
            ])
            ->setAllowedValues('behaviour_array', [
                'always_array',
                'array_if_multiple',
                'always_array',
            ]);

        if ($this->defaultDomain !== null) {
            $resolver->setDefault('domain', $this->defaultDomain);
        }

        return $resolver;
    }
}