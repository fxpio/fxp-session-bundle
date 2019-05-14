<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\SessionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration of bundle.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fxp_session');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->append($this->getSessionNode());

        return $treeBuilder;
    }

    /**
     * Get session node.
     *
     * @return ArrayNodeDefinition
     */
    private function getSessionNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('pdo');
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->getRootNode();

        $node
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->children()
            ->scalarNode('dsn')
            ->defaultValue($this->hasEnvUrl() ? '%env(DATABASE_URL)%' : '%env(DATABASE_DRIVER)%:host=%env(DATABASE_HOST)%;dbname=%env(DATABASE_NAME)%')
            ->info('The DSN of PDO configuration')
            ->end()
            ->end()
            ->children()
            ->arrayNode('db_options')
            ->info('The options of Symfony PDO Handler')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('db_table')
            ->info('The name of session table')
            ->end()
            ->scalarNode('db_id_col')
            ->info('The name session column id')
            ->end()
            ->scalarNode('db_data_col')
            ->info('The name of session column value')
            ->end()
            ->scalarNode('db_lifetime_col')
            ->info('The name of session column lifetime')
            ->end()
            ->scalarNode('db_time_col')
            ->info('The name of session column time')
            ->end()
            ->scalarNode('db_username')
            ->defaultValue($this->hasEnvUrl() ? null : '%env(DATABASE_USER)%')
            ->info('The username of database')
            ->end()
            ->scalarNode('db_password')
            ->defaultValue($this->hasEnvUrl() ? null : '%env(DATABASE_PASSWORD)%')
            ->info('The password of database')
            ->end()
            ->arrayNode('db_connection_options')
            ->prototype('scalar')
            ->end()
            ->end()
            ->scalarNode('lock_mode')
            ->info('The lock mode')
            ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $node;
    }

    private function hasEnvUrl(): bool
    {
        return false !== getenv('DATABASE_URL');
    }
}
