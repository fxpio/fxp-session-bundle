<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SessionBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Sonatra\Bundle\SessionBundle\DependencyInjection\Configuration;

/**
 * Tests case for Configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
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
                    'dsn'        => '%database_driver%:host=%database_host%;dbname=%database_name%',
                    'username'   => '%database_user%',
                    'password'   => '%database_password%',
                    'db_options' => array(
                            'db_table'    => 'session',
                            'db_id_col'   => 'session_id',
                            'db_data_col' => 'session_value',
                            'db_time_col' => 'session_time',
                    ),
            ),
        );
    }
}
