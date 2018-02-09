<?php
declare(strict_types=1);

namespace Xymanek\HashidsBundle\Exception;

class NoDefaultHashidsDomainException extends InvalidDomainException
{
    public function __construct ()
    {
        parent::__construct('Default hashids domain is not set. Please manually specify a domain');
    }
}