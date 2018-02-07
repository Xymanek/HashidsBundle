<?php
declare(strict_types=1);

namespace Xymanek\HashidsBundle\EventListener;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestAttributeListener extends AbstractDecoderListener
{
    const REQUEST_ATTRIBUTE = 'decode_hashids';

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
            $this->decodeFromRequest(
                $request,
                $config['domain'],
                $parameter,
                $config['target'],
                $config['behaviour_invalid'],
                $config['behaviour_array']
            );
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
            ->setAllowedTypes('domain', ['string', 'null'])
            ->setAllowedTypes('target', 'string')
            ->setAllowedValues('behaviour_invalid', [
                self::INVALID_BEHAVIOUR_EXCEPTION,
                self::INVALID_BEHAVIOUR_HTTP_NOT_FOUND,
                self::INVALID_BEHAVIOUR_SET_NULL,
            ])
            ->setAllowedValues('behaviour_array', [
                self::ARRAY_BEHAVIOUR_ALWAYS_FIRST,
                self::ARRAY_BEHAVIOUR_ARRAY_IF_MULTIPLE,
                self::ARRAY_BEHAVIOUR_ALWAYS_ARRAY,
            ]);

        $defaultDomain = $this->registry->getDefaultDomain();

        if ($defaultDomain !== null) {
            $resolver->setDefault('domain', $defaultDomain);
        }

        return $resolver;
    }
}