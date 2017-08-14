<?php
namespace MyTravel\Location\Controller;

use MyTravel\Core\ModuleInterface;

class LocationController implements ModuleInterface {
  protected static $controller;

  protected function __construct() {
    
  }

  public static function load() {
    if (!(self::$controller instanceof LocationController)) {
      self::$controller = new LocationController();
    }
    return self::$controller;
  }

  public static function init() {
    
  }

}
