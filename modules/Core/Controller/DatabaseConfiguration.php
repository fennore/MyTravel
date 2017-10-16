<?php

namespace MyTravel\Core\Controller;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use MyTravel\Core\Event\ConfigNodeEvent;
use MyTravel\Core\CoreEvents;

/**
 * @todo provide information to the config treebuilder.
 * Connection configuration validation is directly linked with Doctrine ORM.
 */
final class DatabaseConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    // Get drivers
    $allowedDrivers = DriverManager::getAvailableDrivers();
    // Build the tree
    $node = $treeBuilder
      ->root('database')
        ->children();
    $node
      ->booleanNode('use_cache')->defaultValue(true)->end()
      ->scalarNode('character_set')->defaultValue('utf8')->end()
      ->scalarNode('collate')->defaultValue('utf8_unicode_ci')->end()
      ->arrayNode('connections')
        ->useAttributeAsKey('name')
      ->defaultValue(array(
        'sqlite' => array(
          'driver' => 'pdo_sqlite',
          'path' => './db-sqlite/mytravel.sqlite',
        )
      ))
      ->prototype('array')
      ->children()
      ->enumNode('driver')
        ->defaultValue('pdo_sqlite')
        ->values($allowedDrivers)
      ->end()
      ->scalarNode('driverClass')->end()
      ->scalarNode('pdo')->end()
      ->scalarNode('dbname')->end()
      ->scalarNode('user')->end()
      ->scalarNode('password')->end()
      ->scalarNode('host')->end()
      ->scalarNode('path')->end()
      ->end()
            ->end()
          ->end()
    ;
    // Dispatch event for altering database config node
    $event = new ConfigNodeEvent($node);
    App::event()->dispatch(CoreEvents::DBCONFIG, $event);
    //
    $node->end();

    return $treeBuilder;
  }

}
