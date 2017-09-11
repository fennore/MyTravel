<?php

namespace MyTravel\Location\Gapi;

use MyTravel\Location\DirectionsDriverInterface;
use MyTravel\Location\Model\Direction;
use MyTravel\Location\Gapi\GapiHelper;
use MyTravel\Location\Gapi\GapiDirectionsRequest;

/**
 * Directions Driver using Google API
 */
class GapiDirectionsDriver implements DirectionsDriverInterface {
  /**
   * Maximum amount of locations sent per 1 direction request
   * @see https://developers.google.com/maps/documentation/javascript/directions#UsageLimits
   */
  const REQUESTSIZE = 25;

  public function getRequestSize() {
    return self::REQUESTSIZE;
  }

  public function getDirections(array $locationList, $maxRequests = 0) {
    $directionList = array();
    // Stop execution when not enough waypoints are available
    if (empty($locationList) || count($locationList) < 2) {
      return $directionList;
    }
    // Set first origin
    $origin = array_shift($locationList);
    $setBack = 0;
    $requestCount = 0;
    while (!empty($locationList) && ($maxRequests === 0 || $requestCount++ < $maxRequests)) {

      $modes = GapiHelper::DIRECTIONMODES;
      // Note: the origin must always be the same for every travel mode
      do {
        $modeSet = array_shift($modes);
        $mode = key($modeSet);
        $size = (current($modeSet) ?? self::REQUESTSIZE) - $setBack;

        $listChunk = array_slice($locationList, 0, $size);
        $destination = array_pop($listChunk);
        $directionRequest = new GapiDirectionsRequest();
        array_map(array($directionRequest, 'addWaypoint'), $listChunk);
        $response = $directionRequest
          ->setMode($mode)
          ->setOrigin($origin)
          ->setDestination($destination)
          ->setAvoid('ferries|tolls|highways')
          ->getDirections();
      } while (empty($response->routes) && !empty($modes) && $requestCount++);

      array_splice($locationList, 0, $size);
      // From now on we reuse one location so we need one less from locationList
      $setBack = 1;
      // Add direction to list
      if (!empty($response->routes)) {
        $direction = new Direction();
        $direction
          ->setOrigin($origin)
          ->setDestination($destination)
          ->setStage($origin->getStage())
          ->setData($response);
        array_push($directionList, $direction);
      }
      // Set next origin as last destination
      $origin = $destination;
    }
    return $directionList;
  }

  /**
   * Get encoded polyline.
   * Currently returning the less accurate overview_polyline.
   * @todo https://stackoverflow.com/questions/16180104/get-a-polyline-from-google-maps-directions-v3
   */
  public function getPolyline(Direction $direction) {
    return (string) $direction->data->routes[0]->overview_polyline->points;
  }

}
