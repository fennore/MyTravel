<?php

namespace MyTravel\Timeline\Model;

use MyTravel\Core\Model\SourceItem;
use MyTravel\Core\Model\Item;
use MyTravel\Core\Controller\Routing;
use MyTravel\Core\Controller\App;

class TimelineItem extends Item {

  use SourceItem;

  protected $property;
  protected $setting;

  /**
   * Set the item path.
   * Overwrite original.
   */
  public function setPath() {
    $prefix = explode('/', Routing::get()
          ->routes()
      ->get('timeline')
      ->getPath())[1];
    $this->path = $prefix . '/' . App::get()->cleanPathString($this->title);
  }

}
