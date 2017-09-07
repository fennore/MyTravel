<?php

namespace MyTravel\Core\Model;

use Symfony\Component\Finder\SplFileInfo;
use MyTravel\Core\Controller\Config;

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

  public function getFullSource() {
    return Config::get()->directories['files'] . '/' . $this->source;
  }

  public function isImage() {
    $support = imagetypes();
    $types = array(
      IMG_JPEG => image_type_to_mime_type(IMAGETYPE_JPEG),
      IMG_PNG => image_type_to_mime_type(IMAGETYPE_PNG),
      IMG_GIF => image_type_to_mime_type(IMAGETYPE_GIF),
      IMG_WBMP => image_type_to_mime_type(IMAGETYPE_WBMP)
    );
    // IMG_BMP | IMG_GIF | IMG_JPG | IMG_PNG | IMG_WBMP | IMG_XPM | IMG_WEBP
    var_dump($types);
    var_dump();
    $bitCheck = array_search($this->type, $types);
    return $support & $bitCheck;
  }

}
