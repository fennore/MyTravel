<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class RoutingConfiguration implements ConfigurationInterface {

  public function getConfigTreeBuilder() {
    $treeBuilder = new TreeBuilder();
    $rootNode = $treeBuilder
      ->root('routing');
    // 1. Collect existing routing from modules
    return $treeBuilder;
  }

}
