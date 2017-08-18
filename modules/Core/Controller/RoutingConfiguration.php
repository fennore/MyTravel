<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use MyTravel\Core\Event\ConfigNodeEvent;

class RoutingConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    $node = $treeBuilder
      ->root('routing')
      ->children();
    // 1. Collect routes
    /**
     * Note!
     * Only paths should be adjustable through the config
     * So only the path should be known in the config tree,
     * for each named node.
     * Only the homepage should have a fixed path but adjustable callback.
     */
    // 2. Set adjustable route info in config tree
    $node
      ->arrayNode('routes')->addDefaultsIfNotSet()->children()
      ->arrayNode('home')->addDefaultsIfNotSet()->children()
      ->arrayNode('callback')->prototype('scalar')->end()->end()
      ->end()->end()
      ->arrayNode('about')->addDefaultsIfNotSet()->children()
      ->scalarNode('path')->defaultValue('about')->end()
      ->end()->end()
      ->arrayNode('imgeditlist')->addDefaultsIfNotSet()->children()
      ->scalarNode('path')->defaultValue('edit/images')->end()
      ->end()->end()
      ->arrayNode('imgfileview')->addDefaultsIfNotSet()->children()
      ->scalarNode('path')->defaultValue('img/[*:title]/[**:trailing]?')->end()
      ->end()->end()
      ->arrayNode('jsbundle')->addDefaultsIfNotSet()->children()
      ->scalarNode('path')->defaultValue('js/bundle')->end()
      ->end()->end()
      ->arrayNode('cssbundle')->addDefaultsIfNotSet()->children()
      ->scalarNode('path')->defaultValue('css/bundle')->end()
      ->end()->end()
      ->end()->end();
    // Dispatch event for altering application config node
    $event = new ConfigNodeEvent($node);
    App::event()->dispatch('module.config.routes', $event);
    //
    return $treeBuilder;
  }

}
