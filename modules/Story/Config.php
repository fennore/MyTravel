<?php

namespace MyTravel\Story\Controller;

use MyTravel\Core\Event\ConfigNodeEvent;

class Config {

  public function applicationDirectories(ConfigNodeEvent $event) {
    $event
      ->node()
        ->scalarNode('stories')->defaultValue('files/stories')->end();
  }

}
