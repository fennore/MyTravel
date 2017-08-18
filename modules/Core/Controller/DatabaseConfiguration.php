<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use MyTravel\Core\Event\ConfigNodeEvent;

class DatabaseConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    // Build the tree
    $node = $treeBuilder
      ->root('database')
        ->children();
    $node
      ->scalarNode('character-set')->defaultValue('utf8')->end()
      ->scalarNode('collate')->defaultValue('utf8_unicode_ci')->end()
      ->arrayNode('connections')
        ->useAttributeAsKey('name')
      ->defaultValue(array(
        'sqlite' => array(
          'driver' => 'sqlite',
          'directory' => 'db-sqlite',
          'dbname' => 'mytravel'
        )
      ))
      ->prototype('array')
      ->children()
      ->enumNode('driver')
      ->defaultValue('sqlite')
      ->values(array('mysql', 'sqlite', 'mssql'))
      ->end()
      ->scalarNode('directory')->defaultValue('db-sqlite')->end()
      ->scalarNode('dbname')->end()
      ->scalarNode('user')->end()
      ->scalarNode('password')->end()
      ->end()
            ->end()
          ->end()
    ;
    // Dispatch event for altering database config node
    $event = new ConfigNodeEvent($node);
    App::event()->dispatch('module.config.database', $event);
    //
    $node->end();

    return $treeBuilder;
  }

}
