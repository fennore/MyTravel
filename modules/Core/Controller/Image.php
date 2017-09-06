<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Controller\ItemController;

class Image {
  public static function view(Request $request) {
    $ctrl = new ItemController();
    $item = $ctrl->getItemByTitle($request);
    var_dump($item->file);
  }

}
