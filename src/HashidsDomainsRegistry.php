<?php
namespace Xymanek\HashidsBundle;

use Hashids\HashidsInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class HashidsDomainsRegistry implements HashidsRegistry
{
    /**
     * @var ServiceLocator
     */
    protected $locator;

    /**
     * @var string|null
     */
    protected $defaultDomain;

    public function __construct (ServiceLocator $locator, string $defaultDomain = null)
    {
        $this->locator = $locator;
        $this->defaultDomain = $defaultDomain;
    }

    public function get (string $domain = null): HashidsInterface
    {
        if ($domain === null) {
            if ($this->defaultDomain === null) {
                throw new \InvalidArgumentException('Default hashids domain not set');
            }

            $domain = $this->defaultDomain;
        }

        return $this->locator->get($domain);
    }
}