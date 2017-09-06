<?php

namespace MyTravel\Timeline\Controller;

use MyTravel\Core\ModuleControllerInterface;
use MyTravel\Core\Controller\App;
use MyTravel\Core\CoreEvents;

final class TimelineController implements ModuleControllerInterface {

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
    $cb = array(new TimelineItemController(), 'cleanGhostFiles');
    App::event()
      ->addListener(CoreEvents::RMFILES, $cb);
  }

}
