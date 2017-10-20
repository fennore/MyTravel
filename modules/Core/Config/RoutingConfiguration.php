<?php

namespace MyTravel\Core\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use MyTravel\Core\Service\Routing;

final class RoutingConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    $node = $treeBuilder
      ->root('routing')
      ->children()
        ->arrayNode('routes')
        ->addDefaultsIfNotSet()
        ->children();
    
    $this->addConfigFromRouting($node);
    $node->end()->end();
    //
    return $treeBuilder;
  }
  /**
   * Adding routes to config so they can be changed there
   * Note!
   * Only paths should be adjustable through the config.
   * So only the path should be known in the config tree, for each named node.
   * Only the homepage should have a fixed path but adjustable callback.
   */
  private function addConfigFromRouting(NodeBuilder $node) {
    // 1. Collect routes
      $routes = Routing::get()
      ->routes()
      ->all();
    // 2. Add routes to config
    // home
    $node->arrayNode('home')->addDefaultsIfNotSet()->children()
      ->arrayNode('callback')->prototype('scalar')->end()->end()
      ->end()->end();
    // rest
    foreach ($routes as $name => $route) {
      // skip home
      if ($name === 'home') {
        continue;
      }
      $node->arrayNode($name)->addDefaultsIfNotSet()->children()
        ->scalarNode('path')->defaultValue($route->getPath())->end()
        ->end()->end();
    }
  }

}
