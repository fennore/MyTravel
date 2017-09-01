<?php

namespace MyTravel\Timeline\Model;

use MyTravel\Core\Model\SourceItem;
use MyTravel\Core\Model\Item;

class TimelineItem extends Item {

  use SourceItem;

  private $property;
  private $setting;

}
