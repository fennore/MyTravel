<?php
namespace MyTravel\Location\Controller;

use MyTravel\Core\ModuleControllerInterface;

final class LocationController implements ModuleControllerInterface {

  protected static $controller;

  protected function __construct() {
    
  }

  public static function load() {
    if (!(self::$controller instanceof self)) {
      self::$controller = new self();
    }
    return self::$controller;
  }

  public function init() {
    
  }

}
