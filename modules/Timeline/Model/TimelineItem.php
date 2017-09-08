<?php

namespace MyTravel\Timeline\Model;

use MyTravel\Core\Model\Item;
use MyTravel\Core\ImageSource;

class TimelineItem extends Item {
  const MIMEMATCH = 'image/%';

  use ImageSource;
}
