<?php

namespace MyTravel\Location\Model;

class Coordinate {

  private $lat;
  private $lng;

  public function setLat(float $lat) {
    $this->lat = $lat;
    return $this;
  }

  public function setLng(float $lng) {
    $this->lng = $lng;
    return $this;
  }

  public function lat() {
    return (float) $this->lat;
  }

  public function lng() {
    return (float) $this->lng;
  }

  public function __toString() {
    return $this->lat() . ',' . $this->lng();
  }

}
