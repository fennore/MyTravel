<?php

namespace MyTravel\Location\Model;

use MyTravel\Location\Model\Location;

class Direction {

  private $id;
  private $origin;
  private $destination;
  private $stage;
  private $data;

  public function __isset($name) {
    return isset($this->$name);
  }

  public function __get($name) {
    return $this->$name;
  }

  public function setOrigin(Location $origin) {
    $this->origin = $origin;
    return $this;
  }

  public function setDestination(Location $destination) {
    $this->destination = $destination;
    return $this;
  }

  public function setStage(int $stage) {
    $this->stage = $stage;
    return $this;
  }

  public function setData($data) {
    $this->data = $data;
    return $this;
  }

}
