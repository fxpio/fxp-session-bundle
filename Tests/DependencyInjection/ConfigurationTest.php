<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SessionBundle\Tests\DependencyInjection;

use Fxp\Bundle\SessionBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Tests case for Configuration.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class ConfigurationTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[]]);

        $this->assertEquals(
            array_merge([], self::getBundleDefaultConfig()),
            $config
        );
    }

    protected static function getBundleDefaultConfig()
    {
        return [
            'pdo' => [
                'enabled' => true,
                'dsn' => '%env(DATABASE_DRIVER)%:host=%env(DATABASE_HOST)%;dbname=%env(DATABASE_NAME)%',
                'db_options' => [
                    'db_username' => '%env(DATABASE_USER)%',
                    'db_password' => '%env(DATABASE_PASSWORD)%',
                    'db_connection_options' => [],
                ],
            ],
        ];
    }
}
