<?php

namespace MyTravel\Core\Model;

use JsonSerializable;
use MyTravel\Core\Controller\App;

class Item implements JsonSerializable {

  // Automatically assigned
  private $id;
  private $status;
  private $weight;
  private $timestamp;
  private $path;
  private $link;
  // User provided
  private $title;
  private $content;

  public function __construct($newData) {
    foreach ($newData as $col => $val) {
      $this->$col = $val;
    }
    $this->path = strtolower((new \ReflectionClass($this))->getShortName()) . '/' . App::get()->cleanPathString($this->title);
  }

  public function jsonSerialize() {
    $vars = get_object_vars($this);
    return $vars;
  }

}
