<?php

namespace MyTravel\Location;

use MyTravel\Core\Event\ThemingEvent;
use MyTravel\Core\Controller\Config;

class ThemingListener {
  public function onLoad(ThemingEvent $event) {
    $event->themer()->addGlobal('directionsdriveraccesskey', Config::get()->directionsdriveraccesskey);
  }

}
