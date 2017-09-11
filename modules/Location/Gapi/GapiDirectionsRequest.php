<?php

namespace MyTravel\Location\Gapi;

use ErrorException;
use MyTravel\Core\Controller\Config;
use MyTravel\Location\Model\Location;

class GapiDirectionsRequest {

  private $mode;
  private $origin;
  private $destination;
  private $waypoints = array();
  private $avoid;

  public function setMode(string $mode) {
    if (!in_array($mode, GapiHelper::VALIDDIRECTIONMODES)) {
      throw new ErrorException(sprintf('Trying to set invalid mode % for GAPI Direction Request.', $mode));
    }
    $this->mode = $mode;
    return $this;
  }

  public function setAvoid(string $avoid) {
    $this->avoid = $avoid;
    return $this;
  }

  public function setOrigin(Location $origin) {
    $this->origin = $origin;
    return $this;
  }

  public function setDestination(Location $destination) {
    $this->destination = $destination;
    return $this;
  }

  public function addWaypoint(Location $waypoint) {
    array_push($this->waypoints, $waypoint);
  }

  private function getKey() {
    if (empty(Config::get()->directionsdriveraccesskey)) {
      throw new ErrorException('Requesting Google API Directions when no Key has been set. Add API key as directionsdriveraccesskey to your config.yml');
    }
    return Config::get()->directionsdriveraccesskey;
  }

  public function getDirections() {

    $url = GapiHelper::DIRECTIONSREQUESTURL . '?' . $this;
    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $url,
      CURLOPT_SSL_VERIFYPEER => false, // ssl verification seems to fail
    ));
    // Send the request & save response to $resp
    $response = json_decode(curl_exec($curl));
    // Close request to clear up some resources
    curl_close($curl);
    // Take a break;
    usleep(100);
    return $response;
  }

  public function __toString() {
    return http_build_query(array(
      'mode' => $this->mode,
      'origin' => (string) $this->origin,
      'destination' => (string) $this->destination,
      'waypoints' => 'via:' . (implode('|via:', $this->waypoints)), //$polylineEncoder->encodedString()
      'avoid' => $this->avoid,
      'key' => $this->getKey(),
    ));
  }

}
