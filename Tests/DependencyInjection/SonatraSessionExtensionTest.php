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
use Sonatra\Bundle\SessionBundle\DependencyInjection\SonatraSessionExtension;
use Sonatra\Bundle\SessionBundle\SonatraSessionBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests case for Extension.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSessionExtensionTest extends TestCase
{
    public function testExtensionExist()
    {
        $container = $this->createContainer();

        $this->assertTrue($container->hasExtension('sonatra_session'));
    }

    public function testExtensionLoader()
    {
        $container = $this->createContainer();

        $this->assertTrue($container->hasParameter('sonatra_session.pdo.dsn'));
        $this->assertTrue($container->hasParameter('sonatra_session.pdo.db_options'));
        $this->assertTrue($container->has('sonatra_session.handler.pdo'));
    }

    public function testExtensionLoaderWithoutPdo()
    {
        $container = $this->createContainer(array('pdo' => array('enabled' => false)));

        $this->assertFalse($container->hasParameter('sonatra_session.pdo.dsn'));
        $this->assertFalse($container->hasParameter('sonatra_session.pdo.db_options'));
        $this->assertFalse($container->has('sonatra_session.handler.pdo'));
    }

    /**
     * @expectedException \Sonatra\Bundle\SessionBundle\Exception\InvalidConfigurationException
     */
    public function testExtensionDsnMissing()
    {
        $this->createContainer(array('pdo' => array('dsn' => null)));
    }

    protected function createContainer(array $config = array())
    {
        $configs = empty($config) ? array() : array($config);
        $container = new ContainerBuilder();
        $container->setParameter('env(DATABASE_DRIVER)', 'pdo_database_driver');
        $container->setParameter('env(DATABASE_HOST)', 'database_host');
        $container->setParameter('env(DATABASE_NAME)', 'database_name');
        $container->setParameter('env(DATABASE_USER)', 'database_user');
        $container->setParameter('env(DATABASE_PASSWORD)', 'database_password');

        $bundle = new SonatraSessionBundle();
        $bundle->build($container);

        $extension = new SonatraSessionExtension();
        $container->registerExtension($extension);
        $extension->load($configs, $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
