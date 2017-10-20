<?php

namespace MyTravel\Core\Model;

use Symfony\Component\Finder\SplFileInfo;
use MyTravel\Core\Service\Config;
use MyTravel\Core\Controller\Image;

class File {

  private $id;
  private $source;
  private $path;
  private $type;
  private $lastmodified;
  /**
   * In some cases we want item info with the file.
   * @todo check if we should use unilateral Doctrine for this instead.
   * @var Item
   */
  private $item;

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
      $this->source = str_replace('\\', '/', $newData->getRelativePathname()); // Always use / for directory separator
      $this->path = str_replace('\\', '/', $newData->getRelativePath());
      $this->type = \mime_content_type($newData->getRealPath());
      $this->lastmodified = $newData->getMTime();
      //$this->data = $newData->getContents();
    }
  }

  public function getFullSource() {
    return Config::get()->directories['files'] . '/' . $this->source;
  }

  /**
   * Check if file is an image,
   * and of supported type.
   * @return string MIME type | FALSE
   */
  public function getSupportedImageType() {
    $bitCheck = array_search($this->type, Image::types());
    return (imagetypes() & $bitCheck) ? $this->type : false;
  }
  
  public function cleanPaths() {
    $this->source = str_replace('\\', '/', $this->source); // Always use / for directory separator
    $this->path = str_replace('\\', '/', $this->path);
  }

}
