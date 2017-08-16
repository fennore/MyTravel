<?php

namespace MyTravel\Story\Controller;

use MyTravel\Core\ModuleControllerInterface;

class StoryController implements ModuleControllerInterface {

  protected static $controller;

  protected function __construct() {

  }

  public static function load() {
    if (!(self::$controller instanceof StoryController)) {
      self::$controller = new StoryController();
    }
    return self::$controller;
  }

  public function init() {
    
  }

}
