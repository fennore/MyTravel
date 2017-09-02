<?php

namespace MyTravel\Core\Model;

use Symfony\Component\Finder\SplFileInfo;

class File {
  private $id;
  private $source;
  private $path;
  private $type;
  private $lastmodified;

  /**
   * Holds the file data.
   * For now not use it.
   * @var type
   */
  private $data;
  public function __isset($name) {
    return isset($this->$name);
  }

  public function __get($name) {
    return $this->$name;
  }

  public function __set($name, $value) {
    $this->$name = $value;
  }

  public function __construct(SplFileInfo $newData = null) {
    if (isset($newData)) {
      $this->source = $newData->getRelativePathname();
      $this->path = $newData->getRelativePath();
      $this->type = \mime_content_type($newData->getRealPath());
      $this->lastmodified = $newData->getMTime();
      //$this->data = $newData->getContents();
    }
  }

}
