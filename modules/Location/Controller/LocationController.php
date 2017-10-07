<?php
namespace MyTravel\Location\Controller;

use MyTravel\Core\ModuleControllerInterface;
use MyTravel\Core\Controller\App;
use MyTravel\Core\CoreEvents;
use MyTravel\Location\ThemingListener;

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
    // Themer event listener
    $themingListener = new ThemingListener();
    App::event()->addListener(CoreEvents::THEMERLOAD, array($themingListener, 'onLoad'));
  }

}
