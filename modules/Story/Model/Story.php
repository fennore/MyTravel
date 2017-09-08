<?php

namespace MyTravel\Story\Model;

use MyTravel\Core\SourceItem;
use MyTravel\Core\Model\Item;

class Story extends Item {

  const MIMEMATCH = array(
    'application/vnd.oasis.opendocument.text'
  );

  use SourceItem;
}
