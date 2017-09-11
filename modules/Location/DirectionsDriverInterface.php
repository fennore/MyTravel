<?php

namespace MyTravel\Location;

use MyTravel\Location\Model\Direction;

/**
 * @todo make a LocationList Iterator to be required as parameter for getDirections
 */
interface DirectionsDriverInterface {
  
  /**
   * Get the amount of locations that will be used in 1 Direction request.
   */
  public function getRequestSize();

  /**
   * Get Route Directions.
   * @param array $locationList List of Location Entities.
   * @param int $maxRequests Maximum Direction requests to send
   * @return array
   */
  public function getDirections(array $locationList, $maxRequests = 0);

  /**
   * Get encoded polyline.
   */
  public function getPolyline(Direction $direction);
}
