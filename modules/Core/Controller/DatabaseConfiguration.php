<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class DatabaseConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    // Build the tree
    $rootNode = $treeBuilder
      ->root('database')
        ->children()
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
        ->end()
    ;

    return $treeBuilder;
  }

}
