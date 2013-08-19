<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SessionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Sonatra\Bundle\SessionBundle\Exception\InvalidConfigurationException;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraSessionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        # Session
        if (isset($config['pdo'])) {
            $loader->load('pdo_storage.yml');
            $pdo = $config['pdo'];

            if (!isset($config['pdo']['dsn'])) {
                throw new InvalidConfigurationException('The "pdo.dsn" parameter under the "sonatra_session" section in the config must be set in order');
            }

            $container->setParameter('sonatra_session.pdo.dsn', $pdo['dsn']);
            $container->setParameter('sonatra_session.pdo.username', $pdo['username']);
            $container->setParameter('sonatra_session.pdo.password', $pdo['password']);
            $container->setParameter('sonatra_session.pdo.db_options', $pdo['db_options']);
        }
    }
}
