<?php
namespace MyTravel\User\Controller;

use MyTravel\Core\ModuleControllerInterface;

class UserController implements ModuleControllerInterface {
  
  protected static $controller;
  
  protected function __construct() {
  }
  
  public static function load() {
    if (!(self::$controller instanceof UserController)) {
      self::$controller = new UserController();
    }
    return self::$controller;
  }
  
  public function init() {
    
  }
}
