<?php

namespace MyTravel\Core\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Event\ConfigNodeEvent;
use MyTravel\Core\CoreEvents;

/**
 *
 */
class ModuleConfiguration implements ConfigurationInterface {
  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    // Build the tree
    $node = $treeBuilder
      ->root('modules')
      ->useAttributeAsKey('name')
      ->prototype('array')
      ->children()
        ->enumNode('status')->values(array('dev', 'prod'))->end()
        ->booleanNode('active')->end();
    // Dispatch event for altering application config node
    $event = new ConfigNodeEvent($node);
    App::event()->dispatch(CoreEvents::APPCONFIG, $event);
    $node->end();
    return $treeBuilder;
  }
  
}
