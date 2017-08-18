<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Routing\RouteCollection;
use MyTravel\Core\ServiceFactoryInterface;

/**
 * Singleton service factory for routing
 */
class Routing implements ServiceFactoryInterface {
  /**
   * Being itself
   * @var MyTravel\Core\Controller\Routing
   */
  protected static $routing;
  /**
   * @var Symfony\Component\Routing\RouteCollection
   */
  private $routes;

  protected function __construct() {
    
  }

  /**
   * Short alias for App::service()->get('routes')
   * @return self
   */
  public static function get() {
    return App::service()->get('routes');
  }

  /**
   * Set as routing service
   * @return self
   */
  public static function setService() {
    if (!(self::$routing instanceof self)) {
      self::$routing = new self();
    }
    return self::$routing;
  }

  public function build() {
    $this->routes = new RouteCollection();
    // Dispatch event for altering application directories config node
    $event = new RoutingEvent($subnode);
    App::event()->dispatch('module.config.application.directories', $event);
  }

  public function routes() {
    return $this->routes;
  }

}
