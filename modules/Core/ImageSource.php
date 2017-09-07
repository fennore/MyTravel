<?php

namespace MyTravel\Core;

use Throwable;
use MyTravel\Core\SourceItem;
use MyTravel\Core\Model\File;

trait ImageSource {

  use SourceItem {
    setFile as sourceItemSetFile;
  }

  protected $property;
  protected $setting;

  /**
   * Overwrite SourceItem setFile.
   * Adding image exif data to property.
   * Setting Item created date to picture taken time.
   * @param File $file
   */
  public function setFile(File $file) {
    $this->sourceItemSetFile($file);
    // Add exif data
    try {
      $this->property = (object) array();
      $exif = @\exif_read_data($this->file->getFullSource(), 'FILE,COMPUTED', true, false);
      $check = array('FILE' => NULL, 'COMPUTED' => NULL);
      $this->property->exif = array_intersect_key($exif, $check);
    } catch (Throwable $ex) {
      // @todo Warning for exif data failure
    }
    // Created date should match date of picture taken
    if (isset($exif['EXIF']['DateTimeOriginal'])) {
      $this->created = strtotime($exif['EXIF']['DateTimeOriginal']);
    } else if (isset($exif['IFD0']['DateTime'])) {
      $this->created = strtotime($exif['IFD0']['DateTime']);
    }
  }

  public function __isset($name) {
    return parent::__isset($name);
  }

  public function __get($name) {
    if ($name === 'property' || $name === 'setting') {
      return (object) $this->$name;
    }
    return parent::__get($name);
  }

}
