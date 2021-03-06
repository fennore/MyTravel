<?php

namespace MyTravel\Core\Model;

use DateTime;
use JsonSerializable;
use Doctrine\Common\Collections\ArrayCollection;
use MyTravel\Core\Controller\App;

class Item implements JsonSerializable {

  // Automatically assigned
  protected $id;
  protected $status;
  protected $weight;
  protected $created;
  protected $updated;
  protected $path;
  protected $link;
  // User provided
  protected $title;
  protected $content;

  public function __construct($newData = array()) {
    $date = new DateTime();
    $timestamp = $date->getTimestamp();
    $this->link = new ArrayCollection();
    $this->status = 1;
    $this->weight = 0;
    foreach ($newData as $col => $val) {
      $this->$col = $val;
    }
    $this->created = $this->updated = $timestamp;
    $this->type = $this->getType();
    $this->setPath();
  }

  public function update($newData = array()) {
    foreach ($newData as $col => $val) {
      $this->$col = $val;
    }
    $this
      ->setUpdated()
      ->setPath();
  }

  public function __isset($name) {
    return isset($this->$name);
  }

  public function __get($name) {
    return $this->$name;
  }

  public function __set($name, $value) {
    $this->$name = $value;
  }

  /**
   * Updates title and path
   */
  public function setTitle($newTitle) {
    $this->title = $newTitle;
    $this->setPath();
    return $this;
  }

  public function setContent($newContent) {
    $this->content = $newContent;
    return $this;
  }

  public function setUpdated() {
    $date = new DateTime();
    $this->updated = $date->getTimestamp();
    return $this;
  }

  /**
   * Set the item path.
   * Should be called on create and update
   */
  public function setPath() {
    $this->path = App::get()->cleanPathString($this->title);
    return $this;
  }

  public function getPath() {
    return $this->path;
  }

  public function getLink() {
    return $this->link;
  }
  
  public function getWeight() {
    return $this->weight;
  }

  public function getType() {
    return strtolower((new \ReflectionClass($this))->getShortName());
  }

  public function getTypeClass() {
    return get_class($this);
  }

  public function jsonSerialize() {
    return get_object_vars($this);
  }

}
