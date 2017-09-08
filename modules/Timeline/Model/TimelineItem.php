<?php

namespace MyTravel\Timeline\Model;

use MyTravel\Core\Model\Item;
use MyTravel\Core\ImageSource;
use MyTravel\Core\SourceItemInterface;

class TimelineItem extends Item implements SourceItemInterface {

  const MIMEMATCH = 'image/%';

  use ImageSource;
}
