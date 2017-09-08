<?php

namespace MyTravel\Core;

use MyTravel\Core\Model\File;

interface SourceItemInterface {

  public function setFile(File $file);

  public function detachFile();

  public static function matchMime();
}
