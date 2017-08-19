<?php

namespace MyTravel\Core\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\RouteCollection;

class RoutingEvent extends Event {

  private $routeCollection;

  public function __construct(RouteCollection $routeCollection) {
    $this->routeCollection = $routeCollection;
  }

  public function routes() {
    return $this->routeCollection;
  }

}
