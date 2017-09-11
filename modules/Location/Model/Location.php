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
    $this->coordinate
      ->setLat((float) $data['lat'])
      ->setLng((float) $data['lng']);
    $this->info = $data['info'];
    $this->weight = (int) $data['weight'];
    $this->status = (int) $data['status'];
    $this->stage = (int) $data['stage'];
  }

  public function __get($name) {
    return $this->name;
  }

  /**
   * Get Location stage
   * @return int
   */
  public function getStage() {
    return (int) $this->stage;
  }

  /**
   * Get Location weight
   * @return int
   */
  public function getWeight() {
    return (int) $this->weight;
  }

  public function __toString() {
    return (string) $this->coordinate;
  }

}
