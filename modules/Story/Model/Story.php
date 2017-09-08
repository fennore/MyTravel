<?php

namespace MyTravel\Story\Model;

use MyTravel\Core\SourceItem;
use MyTravel\Core\SourceItemInterface;
use MyTravel\Core\Model\Item;
use MyTravel\Story\StoryReader;

class Story extends Item implements SourceItemInterface {

  use SourceItem {
    setFile as sourceItemSetFile;
  }

  const MIMEMATCH = array(
    'application/vnd.oasis.opendocument.text',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
  );

  public function setFile(\MyTravel\Core\Model\File $file) {
    $this->sourceItemSetFile($file);
    $reader = new StoryReader($file);
    $this->content = $reader->getContent();
  }

}
