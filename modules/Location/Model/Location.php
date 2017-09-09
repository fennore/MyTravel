<?php

namespace MyTravel\Location\Model;

class Location {

  protected $id;
  protected $coordinate;
  protected $info;
  protected $weight;
  protected $status;
  protected $stage;

  public function __construct($data) {
    $this->coordinate = new Coordinate();
    $this->coordinate->setLat((float) $data['lat']);
    $this->coordinate->setLng((float) $data['lng']);
    $this->info = $data['info'];
    $this->weight = (int) $data['weight'];
    $this->status = (int) $data['status'];
    $this->stage = (int) $data['stage'];
  }

}
