<?php

namespace MyTravel\Core\Controller;

class Routing {

  protected $routing;
  private $routes;

  protected function __construct() {

  }

  /**
   * Interact with the application.
   * This simply returns the application object.
   * @return App
   */
  public static function get() {
    return self::$routing;
  }

  /**
   * Load the application.
   * This has to be called before doing anything else that
   * concerns the application object.
   * @return self
   */
  public static function load() {
    if (!(self::$routing instanceof Routing)) {
      self::$routing = new Routing();
    }
    return self::$routing;
  }

  public function getRoutes() {
    return $this->routes;
  }

}
