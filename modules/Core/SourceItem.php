<?php

/*
  Removed doctrine mapping file because it doesn't work properly :(
  mappedSuperclass + class table inheritance no worky!
  #This does not work in combination with Class Inheritance
  #As doctrine will read X extending SourceItem extending Item differently
  #Looking for a non existing SourceItem table (report as bug?)
  #MyTravel\Core\Model\SourceItem:
  #  type: mappedSuperclass
  #  oneToOne:
  #    file:
  #      targetEntity: MyTravel\Core\Model\File
  #      joinColumn:
  #        name: fileId
  # */

namespace MyTravel\Core\Model;

use MyTravel\Core\Model\File;
use MyTravel\Core\Model\Item;

trait SourceItem {

  protected $file;

  public function __construct($newData = array()) {
    // Do not allow changes to file
    unset($newData['file']);
    parent::__construct($newData);
  }

  public function __set($name, $value) {
    // Do not change file manually
    if ($name === 'file') {
      // @todo maybe set notification file can not be changed manually
      return;
    }
    parent::__set($name, $value);
  }

  public function setFile(File $file) {
    $this->file = $file;
    // Update title
    if (empty($this->title) && $this instanceof Item) {
      $this->setTitle(pathinfo($this->file->source)['filename']);
    }
  }

}
