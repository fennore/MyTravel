<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Controller\ItemController;

class Image {
  public static function view(Request $request) {
    $ctrl = new ItemController();
    $item = $ctrl->getItemByTitle($request);
    $file = $item->file;
    // Add external info to file for output
    $file->property = $item->property;
    $file->setting = $item->setting;
    return $file;
  }

}
