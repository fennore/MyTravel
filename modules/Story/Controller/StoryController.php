<?php

namespace MyTravel\Story\Controller;

use MyTravel\Core\ModuleControllerInterface;
use MyTravel\Core\Controller\App;

class StoryController implements ModuleControllerInterface {

  protected static $controller;

  protected function __construct() {

  }

  private function addListeners() {
    // Callback
    $cb = array(new StoryConfig(), 'applicationDirectories');
    App::event()
      ->addListener('module.config.application.directories', $cb);
  }

  public static function load() {
    if (!(self::$controller instanceof self)) {
      self::$controller = new self();
      self::$controller->addListeners();
    }
    return self::$controller;
  }

  public function init() {
    
  }

}
