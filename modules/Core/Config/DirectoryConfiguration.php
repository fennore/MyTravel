<?php

namespace MyTravel\Core\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Event\ConfigNodeEvent;
use MyTravel\Core\CoreEvents;

/**
 */
class DirectoryConfiguration implements ConfigurationInterface {
  
  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    // Build the tree
    $node = $treeBuilder
      ->root('directories')
      ->addDefaultsIfNotSet()
      ->children();
    $node
        ->scalarNode('files')->defaultValue('files')->end()
        ->scalarNode('images')->defaultValue('files/images')->end()
        ->scalarNode('views')->defaultValue('views')->end();

    // Dispatch event for altering application directories config node
    $event = new ConfigNodeEvent($node);
    App::event()->dispatch(CoreEvents::DIRCONFIG, $event);
    $node->end();
    return $treeBuilder;
  }
}
