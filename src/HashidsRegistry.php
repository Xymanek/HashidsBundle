<?php
declare(strict_types=1);

namespace Xymanek\HashidsBundle;

use Hashids\HashidsInterface;
use Xymanek\HashidsBundle\Exception\InvalidDomainException;

interface HashidsRegistry
{
    /**
     * @param string|null $domain Null for default domain
     * @return HashidsInterface
     *
     * @throws InvalidDomainException
     */
    public function get (string $domain = null): HashidsInterface;

    /**
     * @return string|null
     */
    public function getDefaultDomainName ();
}