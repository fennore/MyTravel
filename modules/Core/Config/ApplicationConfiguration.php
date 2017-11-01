<?php

namespace MyTravel\Core\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class ApplicationConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    // Build the tree
    $node = $treeBuilder
      ->root('application')
      ->children();
    $node
      ->enumNode('environment')->defaultValue('prod')
        ->values(array('dev', 'staging', 'prod'))
        ->info('Which environment the application currently runs (dev|staging|prod).')
      ->end()
      ->scalarNode('appname')->defaultValue('MyTravel')->end()
      ->scalarNode('view')->defaultValue('default')->end()
      ->integerNode('pagecachetime')
        ->defaultValue(60*60*24)
        ->info('Cache expiration time in seconds.')
      ->end()
    ->end();
    return $treeBuilder;
  }

}
