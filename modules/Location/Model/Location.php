<?php

namespace MyTravel\Location\Model;

class Location {
  private $id;
  private $coordinate;
  private $info;
  private $weight;
  private $status;
  private $stage;

  public function __construct() {
    $this->coordinate = new Coordinate();
  }

}
