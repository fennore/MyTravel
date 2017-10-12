<?php

namespace MyTravel\Timeline\Model;

use MyTravel\Core\Model\Item;
use MyTravel\Core\ImageSource;
use MyTravel\Core\SourceItemInterface;

class TimelineItem extends Item implements SourceItemInterface {

  const MIMEMATCH = 'image/%';

  use ImageSource {
    jsonSerialize as jsonSerializeSource;
  }
  
  public function jsonSerialize() {
    /**
     * Quick and dirty story link
     */
    if($this->getLink()->get(0)) {
      $this->storylink = $this->getLink()->first();
    }
    
    return $this->jsonSerializeSource();
  }
}
