<?php

namespace MyTravel\Timeline\Controller;

use MyTravel\Core\Controller\App;
use Symfony\Component\HttpFoundation\Request;

class PageFactory {

  public static function viewTimeline(Request $request) {
    // Sync timeline items
    if (App::get()->inDevelopment()) {
      $controller = new TimelineItemController();
      $controller->sync();
    }
    // Do the Core PageFactory thing
    return \MyTravel\Core\Controller\PageFactory::viewItemPage($request);
  }

}
