<?php

namespace MyTravel\Story\Controller;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use MyTravel\Core\Event\RoutingEvent;

class Routing {
  public function build(RoutingEvent $event) {
    // Load routes from yml file
    $locator = new FileLocator(array('./modules/Story'));
    $loader = new YamlFileLoader($locator);
    // Add routes to collection
    $event
      ->routes()
      ->addCollection($loader->load('routes.yml'));
  }

}
