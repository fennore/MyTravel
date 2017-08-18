<?php

namespace MyTravel\Core\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class ConfigNodeEvent extends Event {
  private $configNode;

  public function __construct(NodeBuilder $node) {
    $this->configNode = $node;
  }

  public function node() {
    return $this->configNode;
  }

}
