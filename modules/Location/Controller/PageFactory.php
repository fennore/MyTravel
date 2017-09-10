<?php

namespace MyTravel\Location\Controller;

use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Model\Page;

class PageFactory {

  public static function viewLocations(Request $request) {
    // Sync story items
    if (App::get()->inDevelopment()) {
      $ctrlLocations = new LocationEntityController();
      $ctrlLocations->sync();
      $ctrlRoute = new RouteController();
      $ctrlRoute->calculateEncodedRoute();
    }

    return new Page();
  }

}
