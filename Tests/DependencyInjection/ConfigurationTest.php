<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SessionBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sonatra\Bundle\SessionBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * Tests case for Configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConfigurationTest extends TestCase
{
    public function testDefaultConfig()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), array(array()));

        $this->assertEquals(
                array_merge(array(), self::getBundleDefaultConfig()),
                $config
        );
    }

    protected static function getBundleDefaultConfig()
    {
        return array(
            'pdo' => array(
                'enabled' => true,
                'dsn' => '%env(DATABASE_DRIVER)%:host=%env(DATABASE_HOST)%;dbname=%env(DATABASE_NAME)%',
                'db_options' => array(
                    'db_username' => '%env(DATABASE_USER)%',
                    'db_password' => '%env(DATABASE_PASSWORD)%',
                    'db_connection_options' => array(),
                ),
            ),
        );
    }
}
