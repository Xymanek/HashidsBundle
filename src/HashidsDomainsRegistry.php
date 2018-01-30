<?php
namespace Xymanek\HashidsBundle;

use Symfony\Component\DependencyInjection\ServiceLocator;

class HashidsDomainsRegistry
{
    /**
     * @var ServiceLocator
     */
    protected $locator;

    /**
     * @var string
     */
    protected $defaultDomain;

    public function __construct (ServiceLocator $locator, $defaultDomain)
    {
        $this->locator = $locator;
        $this->defaultDomain = $defaultDomain;
    }

    /**
     * @param string|null $domain
     * @return \Hashids\Hashids
     */
    public function get (string $domain = null)
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