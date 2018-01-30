<?php
declare(strict_types=1);

namespace Tests\Xymanek\HashidsBundle\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Xymanek\HashidsBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration ()
    {
        return new Configuration();
    }

    public function testDefault ()
    {
        $this->assertProcessedConfigurationEquals([], [
            'default_domain' => 'default',
            'domains' => [
                'default' => [
                    'salt' => '',
                    'min_hash_length' => 0,
                    'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
                ]
            ],
            'listeners' => [
                'annotations' => true,
            ],
        ]);
    }
}
