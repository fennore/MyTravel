<?php

namespace MyTravel\Timeline\Model;

use Throwable;
use MyTravel\Core\Model\SourceItem;
use MyTravel\Core\Model\Item;
use MyTravel\Core\Model\File;
use MyTravel\Core\Controller\Routing;
use MyTravel\Core\Controller\App;

class TimelineItem extends Item {

  use SourceItem {
    setFile as sourceItemSetFile;
  }

  protected $property;
  protected $setting;
  protected $imagepath;

  public function setFile(File $file) {
    $this->sourceItemSetFile($file);
    // Add exif data
    try {
      $this->property = (object) array();
      $exif = @\exif_read_data($this->file->getFullSource(), 'FILE,COMPUTED', true, false);
      $check = array('FILE' => NULL, 'COMPUTED' => NULL);
      $this->property->exif = array_intersect_key($exif, $check);
    } catch (Throwable $ex) {
      // @todo warn user they should not use special characters for image titles
    }
    // Created date should match date of picture taken
    if (isset($exif['EXIF']['DateTimeOriginal'])) {
      $this->created = strtotime($exif['EXIF']['DateTimeOriginal']);
    } else if (isset($exif['IFD0']['DateTime'])) {
      $this->created = strtotime($exif['IFD0']['DateTime']);
    }
  }

  public function __isset($name) {
    if ($name === 'imagepath') {
      return true;
    }
    return parent::__isset($name);
  }

  public function __get($name) {
    if ($name === 'property' || $name === 'setting') {
      return (object) $this->property;
    }
    if ($name === 'imagepath' && !isset($this->$name)) {
      return $this->getNewImagePath();
    }
    return parent::__get($name);
  }

  /**
   * Set the item path.
   * Overwrite original.
   */
  public function setPath() {
    $prefix = explode('/', Routing::get()
          ->routes()
      ->get('timeline')
      ->getPath())[1];
    $this->path = trim($prefix . '/' . App::get()->cleanPathString($this->title), '/');
    $this->imagepath = $this->getNewImagePath();
  }

  protected function getNewImagePath() {
    return trim('img/' . App::get()->cleanPathString($this->title), '/');
  }

}
