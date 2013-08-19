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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sonatra\Bundle\SessionBundle\SonatraSessionBundle;
use Sonatra\Bundle\SessionBundle\DependencyInjection\SonatraSessionExtension;

/**
 * Tests case for Extension.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSessionExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testExtensionExist()
    {
        $container = $this->createContainer();

        $this->assertTrue($container->hasExtension('sonatra_session'));
    }

    public function testExtensionLoader()
    {
        $container = $this->createContainer();
        $ext = $container->getExtension('sonatra_session');

        $ext->load(array(), $container);

        $this->assertTrue($container->hasParameter('sonatra_session.pdo.dsn'));
        $this->assertTrue($container->hasParameter('sonatra_session.pdo.username'));
        $this->assertTrue($container->hasParameter('sonatra_session.pdo.password'));
        $this->assertTrue($container->hasParameter('sonatra_session.pdo.db_options'));
    }

    public function testExtensionDsnMissing()
    {
        $container = $this->createContainer();
        $ext = $container->getExtension('sonatra_session');

        $this->setExpectedException('\Exception', 'The "pdo.dsn" parameter under the "sonatra_session" section in the config must be set in order');
        $ext->load(array(array('pdo' => array('dsn' => null))), $container);
    }

    protected function createContainer()
    {
        $container = new ContainerBuilder();
        $session = new SonatraSessionExtension();
        $container->registerExtension($session);

        $bundle = new SonatraSessionBundle();
        $bundle->build($container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        //$container->compile();
        return $container;
    }
}
