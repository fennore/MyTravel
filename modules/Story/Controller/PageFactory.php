<?php

namespace MyTravel\Story\Controller;

use MyTravel\Core\Controller\App;
use MyTravel\Core\Controller\FileController;
use Symfony\Component\HttpFoundation\Request;

class PageFactory {

  public static function viewStory(Request $request) {
    // Sync story items
    if (App::get()->inDevelopment()) {
      $controller = new FileController();
      $controller->sync('MyTravel\Story\Model\Story');
    }
    // Do the Core PageFactory thing
    return \MyTravel\Core\Controller\PageFactory::viewItemPage($request);
  }

}
