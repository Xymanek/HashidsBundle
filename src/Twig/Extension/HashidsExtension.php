<?php
namespace Xymanek\HashidsBundle\Twig\Extension;

use Xymanek\HashidsBundle\HashidsDomainsRegistry;

class HashidsExtension extends \Twig_Extension
{
    /**
     * @var HashidsDomainsRegistry
     */
    protected $registry;

    public function __construct (HashidsDomainsRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getFilters ()
    {
        return [
            new \Twig_SimpleFilter('hashid_encode', [$this, 'encode']),
            new \Twig_SimpleFilter('hashid_decode', [$this, 'decode']),

            new \Twig_SimpleFilter('hashids_encode', [$this, 'encode']),
            new \Twig_SimpleFilter('hashids_decode', [$this, 'decode']),
        ];
    }

    public function encode ($number, string $domain = null)
    {
        return $this->registry->get($domain)->encode($number);
    }

    public function decode ($hash, string $domain = null)
    {
        return $this->registry->get($domain)->decode($hash);
    }
}
