<?php

namespace MyTravel\Core\Controller;

use ErrorException;
use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Controller\ItemController;
use MyTravel\Core\Model\ImageDataBag;

class Image {

  /**
   * Return image types supported
   * @return array
   */
  public static function types() {
    return array(
      IMG_JPEG => image_type_to_mime_type(IMAGETYPE_JPEG),
      IMG_PNG => image_type_to_mime_type(IMAGETYPE_PNG),
      // IMG_GIF => image_type_to_mime_type(IMAGETYPE_GIF),
      // IMG_WBMP => image_type_to_mime_type(IMAGETYPE_WBMP)
    );
  }

  /**
   * @todo Implement adjustable image settings
   * @param Request $request
   * @return type
   */
  public static function view(Request $request) {
    $ctrl = new ItemController();
    $item = $ctrl->getItemByTitle($request);
    // Play the reverse game: START!
    $file = clone $item->file;
    if (!$file->getSupportedImageType()) {
      throw new ErrorException('This is not a proper image request.');
    }
    // Trailing
    $trailing = $request->attributes->get('trailing');
    // Remove file link from item
    $item->detachFile();
    // Add item to file for output
    $file->item = $item;
    // EXIF data
    $exif = $item->property->exif;
    $bag = new ImageDataBag();
    // Set crop
    if ($trailing === 'thumbnail') {
      $bag->cw = 160;
      $bag->ch = 120;
    }
    // Bounds
    $bag->w = ($trailing === 'thumbnail') ? 160 : 1920;
    $bag->h = ($trailing === 'thumbnail') ? 120 : 1080;
    // Originals
    $bag->ow = $exif['COMPUTED']['Width'];
    $bag->oh = $exif['COMPUTED']['Height'];
    // Set quality
    $bag->q = ($trailing === 'thumbnail') ? 75 : 84;
    // Recalculate
    $bag->keepRatio();
    // Set img data
    $file->imgdata = $bag;
    return $file;
  }

}
