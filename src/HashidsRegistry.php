<?php
declare(strict_types=1);

namespace Xymanek\HashidsBundle;

use Hashids\HashidsInterface;

interface HashidsRegistry
{
    public function get (string $domain = null): HashidsInterface;
}