<?php
declare(strict_types=1);

namespace Xymanek\HashidsBundle;

use Hashids\HashidsInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Xymanek\HashidsBundle\Exception\InvalidDomainException;

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

    /**
     * @param string|null $domain
     * @return HashidsInterface
     *
     * @throws InvalidDomainException
     * @throws ServiceCircularReferenceException
     */
    public function get (string $domain = null): HashidsInterface
    {
        if ($domain === null) {
            if ($this->defaultDomain === null) {
                throw new InvalidDomainException('Default hashids domain is not set');
            }

            $domain = $this->defaultDomain;
        }

        try {
            return $this->locator->get($domain);
        } catch (ServiceNotFoundException $e) {
            throw new InvalidDomainException("Hashids domain $domain does not exist", 0, $e);
        }
    }

    /**
     * @return string|null
     */
    public function getDefaultDomain ()
    {
        return $this->defaultDomain;
    }
}