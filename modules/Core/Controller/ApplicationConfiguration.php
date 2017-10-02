<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use MyTravel\Core\Event\ConfigNodeEvent;
use MyTravel\Core\CoreEvents;

final class ApplicationConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    // Build the tree
    $node = $treeBuilder
      ->root('application')
      ->children();
    $node
      ->enumNode('environment')->defaultValue('dev')
        ->values(array('dev', 'staging', 'prod'))
      ->info('Which environment the application currently runs (dev|staging|prod).')
      ->end()
      ->scalarNode('appname')->defaultValue('MyTravel')->end()
      ->scalarNode('view')->defaultValue('default')->end()
      ->arrayNode('modules')
      ->useAttributeAsKey('name')
      ->prototype('array')
      ->children()
      ->enumNode('status')->values(array('dev', 'prod'))->end()
      ->booleanNode('active')->end()
      ->end()
      ->end()
    ;
    // Dispatch event for altering application config node
    $event = new ConfigNodeEvent($node);
    App::event()->dispatch(CoreEvents::APPCONFIG, $event);
    // Directories
    $this->buildDirectoryNode($node);
    $node->end();
    return $treeBuilder;
  }

  /**
   * Build the directories configuration.
   * @todo Modules can add there own directories to the list.
   * @param Symfony\Component\Config\Definition\Builder\NodeBuilder $node
   */
  private function buildDirectoryNode($node) {
    $subnode = $node
      ->arrayNode('directories')
        ->addDefaultsIfNotSet()
        ->children()
          ->scalarNode('files')->defaultValue('files')->end()
        ->scalarNode('images')->defaultValue('files/images')->end()
        ->scalarNode('views')->defaultValue('views')->end();

    // Dispatch event for altering application directories config node
    $event = new ConfigNodeEvent($subnode);
    App::event()->dispatch(CoreEvents::DIRCONFIG, $event);
    // Close the sub tree branches
    $node
      ->end()
      ->end();
  }

}
