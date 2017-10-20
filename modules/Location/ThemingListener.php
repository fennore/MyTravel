<?php

namespace MyTravel\Location;

use MyTravel\Core\Event\ThemingEvent;
use MyTravel\Core\Service\Config;

class ThemingListener {
  public function onLoad(ThemingEvent $event) {
    $event->themer()->addGlobal('directionsdriveraccesskey', Config::get()->modules['location']['directionsdriveraccesskey']);
  }

}
