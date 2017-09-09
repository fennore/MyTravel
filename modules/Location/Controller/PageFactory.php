<?php

namespace MyTravel\Location\Controller;

use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Model\Page;

class PageFactory {

  public static function viewLocations(Request $request) {
    // Sync story items
    if (App::get()->inDevelopment()) {
      $ctrl = new LocationEntityController();
      $ctrl->sync();
    }

    // return new Page();
  }

}
