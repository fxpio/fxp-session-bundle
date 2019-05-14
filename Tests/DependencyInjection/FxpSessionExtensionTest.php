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

use Fxp\Bundle\SessionBundle\DependencyInjection\FxpSessionExtension;
use Fxp\Bundle\SessionBundle\FxpSessionBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests case for Extension.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 *
 * @internal
 */
final class FxpSessionExtensionTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('DATABASE_DRIVER');
        putenv('DATABASE_HOST');
        putenv('DATABASE_NAME');
        putenv('DATABASE_USER');
        putenv('DATABASE_PASSWORD');
    }

    public function testExtensionExist(): void
    {
        $container = $this->createContainer();

        $this->assertTrue($container->hasExtension('fxp_session'));
    }

    public function testExtensionLoader(): void
    {
        $container = $this->createContainer();

        $this->assertTrue($container->hasParameter('fxp_session.pdo.dsn'));
        $this->assertTrue($container->hasParameter('fxp_session.pdo.db_options'));
        $this->assertTrue($container->has('fxp_session.handler.pdo'));
    }

    public function testExtensionLoaderWithEnvVariables(): void
    {
        putenv('DATABASE_DRIVER=database_driver');
        putenv('DATABASE_HOST=database_host');
        putenv('DATABASE_NAME=database_name2');

        $container = $this->createContainer([
            'pdo' => [
                'dsn' => '%env(DATABASE_DRIVER)%:host=%env(DATABASE_HOST)%;dbname=%env(DATABASE_NAME)%',
            ],
        ]);

        $this->assertTrue($container->hasParameter('fxp_session.pdo.dsn'));
        $this->assertTrue($container->hasParameter('fxp_session.pdo.db_options'));
        $this->assertTrue($container->has('fxp_session.handler.pdo'));

        $dsn = $container->getParameter('fxp_session.pdo.dsn');

        putenv('DATABASE_DRIVER=');
        putenv('DATABASE_HOST=');
        putenv('DATABASE_NAME=');

        $this->assertSame('database_driver:host=database_host;dbname=database_name2', $dsn);
    }

    public function testExtensionLoaderWithEnvVariablesAndDatabaseUrl(): void
    {
        putenv('DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name');

        $container = $this->createContainer();

        $this->assertTrue($container->hasParameter('fxp_session.pdo.dsn'));
        $this->assertTrue($container->hasParameter('fxp_session.pdo.db_options'));
        $this->assertTrue($container->has('fxp_session.handler.pdo'));

        $dsn = $container->getParameter('fxp_session.pdo.dsn');

        putenv('DATABASE_URL=');

        $this->assertSame('mysql://db_user:db_password@127.0.0.1:3306/db_name', $dsn);
    }

    public function testExtensionLoaderWithEnvDatabaseUrlVariablesAndCustomDsn(): void
    {
        putenv('DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name');

        $container = $this->createContainer([
            'pdo' => [
                'dsn' => '%env(DATABASE_URL)%',
            ],
        ]);

        $this->assertTrue($container->hasParameter('fxp_session.pdo.dsn'));
        $this->assertTrue($container->hasParameter('fxp_session.pdo.db_options'));
        $this->assertTrue($container->has('fxp_session.handler.pdo'));

        $dsn = $container->getParameter('fxp_session.pdo.dsn');

        putenv('DATABASE_URL=');

        $this->assertSame('mysql://db_user:db_password@127.0.0.1:3306/db_name', $dsn);
    }

    public function testExtensionLoaderWithoutPdo(): void
    {
        $container = $this->createContainer(['pdo' => ['enabled' => false]]);

        $this->assertFalse($container->hasParameter('fxp_session.pdo.dsn'));
        $this->assertFalse($container->hasParameter('fxp_session.pdo.db_options'));
        $this->assertFalse($container->has('fxp_session.handler.pdo'));
    }

    public function testExtensionDsnMissing(): void
    {
        $this->expectException(\Fxp\Bundle\SessionBundle\Exception\InvalidConfigurationException::class);

        $this->createContainer(['pdo' => ['dsn' => null]]);
    }

    protected function createContainer(array $config = [])
    {
        $configs = empty($config) ? [] : [$config];
        $container = new ContainerBuilder();
        $container->setParameter('env(DATABASE_DRIVER)', 'pdo_database_driver');
        $container->setParameter('env(DATABASE_HOST)', 'database_host');
        $container->setParameter('env(DATABASE_NAME)', 'database_name');
        $container->setParameter('env(DATABASE_USER)', 'database_user');
        $container->setParameter('env(DATABASE_PASSWORD)', 'database_password');

        $bundle = new FxpSessionBundle();
        $bundle->build($container);

        $extension = new FxpSessionExtension();
        $container->registerExtension($extension);
        $extension->load($configs, $container);

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile(true);

        return $container;
    }
}
