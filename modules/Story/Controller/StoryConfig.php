<?php

namespace MyTravel\Story\Controller;

use Symfony\Component\EventDispatcher\Event;

class StoryConfig {

  public function applicationDirectories(Event $event) {
    $event
      ->node()
        ->scalarNode('stories')->defaultValue('files/stories')->end();
  }

}
