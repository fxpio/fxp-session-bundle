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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration of bundle.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonatra_session');
        $rootNode->append($this->getSessionNode());

        return $treeBuilder;
    }

    /**
     * Get session node.
     *
     * @return ArrayNodeDefinition
     */
    private function getSessionNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('pdo');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('dsn')
                    ->defaultValue('%database_driver%:host=%database_host%;dbname=%database_name%')
                    ->info('The DSN of PDO configuration')
                ->end()
            ->end()
            ->children()
                ->scalarNode('username')
                    ->defaultValue('%database_user%')
                    ->info('The username of database')
                ->end()
            ->end()
            ->children()
                ->scalarNode('password')
                    ->defaultValue('%database_password%')
                    ->info('The password of database')
                ->end()
            ->end()
            ->children()
                ->arrayNode('db_options')
                    ->info('The name of table and columns in database')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('db_table')
                            ->defaultValue('session')
                            ->info('The name of session table')
                        ->end()
                        ->scalarNode('db_id_col')
                            ->defaultValue('session_id')
                            ->info('The name session column id')
                        ->end()
                        ->scalarNode('db_data_col')
                            ->defaultValue('session_value')
                            ->info('The name of session column value')
                        ->end()
                        ->scalarNode('db_time_col')
                            ->defaultValue('session_time')
                            ->info('The name of session column time')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
