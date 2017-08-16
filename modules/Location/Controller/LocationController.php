<?php
namespace MyTravel\Location\Controller;

use MyTravel\Core\ModuleControllerInterface;

class LocationController implements ModuleControllerInterface {

  protected static $controller;

  protected function __construct() {
    
  }

  public static function load() {
    if (!(self::$controller instanceof LocationController)) {
      self::$controller = new LocationController();
    }
    return self::$controller;
  }

  public function init() {
    
  }

}
