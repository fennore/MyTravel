<?php

namespace MyTravel\Location\Model;

class Coordinate {

  private $lat;
  private $lng;

  public function setLat(float $lat) {
    $this->lat = $lat;
  }

  public function setLng(float $lng) {
    $this->lng = $lng;
  }

}
