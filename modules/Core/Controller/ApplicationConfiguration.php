<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ApplicationConfiguration implements ConfigurationInterface {

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
      ->scalarNode('basepath')->defaultValue('/')->end()
      ->scalarNode('theme')->defaultValue('default')->end()
      ->arrayNode('modules')
      ->useAttributeAsKey('name')
      ->prototype('array')
      ->children()
      ->enumNode('status')->values(array('dev', 'prod'))->end()
      ->booleanNode('active')->end()
      ->end()
      ->end()
    ;
    // Directories
    // Expandable by modules
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
    $defaultDirs = array(
      'files' => 'files',
      'images' => 'files/images',
      'themes' => 'themes'
    );
    $subnode = $node
      ->arrayNode('directories')
      ->addDefaultsIfNotSet()
      ->children()
    ;
    foreach ($defaultDirs as $ref => $dir) {
      $subnode->scalarNode($ref)->defaultValue($dir)->end();
    }
    // Close the sub tree branches
    $node
      ->end()
      ->end();
  }

}
