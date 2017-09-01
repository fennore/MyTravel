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

trait SourceItem {

  private $itemId;
  private $file;

}
