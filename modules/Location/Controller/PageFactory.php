<?php

namespace MyTravel\Location\Controller;

use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Model\Page;

class PageFactory {

  public static function viewLocations(Request $request) {
    $ctrlLocations = new LocationEntityController();
    // Sync story items
    if (App::get()->inDevelopment()) {
      $ctrlLocations->sync();
      $ctrlRoute = new RouteController();
      $ctrlRoute->buildEncodedRoute();
    }
    $variables = array(
      'stages' => range(1, $ctrlLocations->getLastStage()),
      'locationlist' => $ctrlLocations->getStageLocations($request->attributes->get('stage'), 0)
    );
    return new Page('locations.tpl', $variables);
  }

}
