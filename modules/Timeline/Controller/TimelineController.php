<?php

namespace MyTravel\Timeline\Controller;

use MyTravel\Core\ModuleInterface;

class TimelineController implements ModuleInterface {
  protected static $controller;

  protected function __construct() {

  }

  public static function load() {
    if (!(self::$controller instanceof TimelineController)) {
      self::$controller = new TimelineController();
    }
    return self::$controller;
  }

  public function init() {
    
  }

}
