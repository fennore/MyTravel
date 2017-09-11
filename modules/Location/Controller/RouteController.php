<?php

namespace MyTravel\Location\Controller;

use MyTravel\Core\Controller\Config;
use MyTravel\Core\Controller\Db;
use MyTravel\Core\Controller\SavedStateController;
use MyTravel\Location\Controller\LocationEntityController;
use MyTravel\Location\Model\Direction;

/**
 * @todo make route controller independent from google
 *       by using intermediate controller (driver?) implementing interface
 *       to get direction data (no specifics of what direction data exactly is, can be many things)
 *       which can be converted to polyline to print on a (google) map
 */
class RouteController {

  const STATEROUTE = 874;
  const STATEDIRECTIONS = 589;

  /**
   * Because it's a lucky number? Nothing can ever go wrong!
   */
  const MAXREQUESTS = 13;

  /**
   *
   * @var \MyTravel\Location\DirectionsDriverInterface
   */
  private $driver;

  /**
   * Use a DirectionsDriver to calculate a route from Location entities.
   * @param DirectionsDriverInterface $driver
   *    Set custom Direction Driver or use from config.
   *    Defaults to \MyTravel\Location\Gapi\GapiDirectionsDriver.
   */
  public function __construct(DirectionsDriverInterface $driver = null) {
    if (empty($driver)) {
      $directionsDriverClass = Config::get()->directionsdriver;
      $driver = new $directionsDriverClass();
    }
    $this->driver = $driver;
  }

  /**
   * The maximum amount of Location entities that will be used for direction requests.
   * @return int
   */
  private function getLocationLimit() {
    return (int) $this->driver->getRequestSize() * self::MAXREQUESTS;
  }

  /**
   * Resets encoded route to certain point,
   * so that it can be rebuild in chunks.
   * @todo See if we can use some direction/location diff to perform a more accurate reset.
   * @param int $stage
   */
  public function resetEncodedRoute(int $stage) {
    // 1. Reset Direction SavedState to weight 0 and stage $stage
    // 2. Update Route SavedState removing all encoded routes of stage $stage only
  }

  /**
   * Build an encoded route in chunks from all Location entities.
   * @return type
   */
  public function buildEncodedRoute() {
    // 1. Get states
    $stateCtrl = new SavedStateController();
    $directionState = $stateCtrl->get(self::STATEDIRECTIONS);
    $routeState = $stateCtrl->get(self::STATEROUTE);
    $stage = (int) ($directionState->get('stage') ?? 1);
    // 2. Get Locations
    $ctrl = new LocationEntityController();
    $locationList = $ctrl->getStageLocations(
      $stage
      , $directionState->get('weight') ?? 0
      , $this->getLocationLimit()
    );
    // - Stop processing when no locations are found and last stage is also empty
    if (empty($locationList) && $stage > $ctrl->getLastStage()) {
      return;
    }
    // - Stop processing when weight is 0 but there are already encoded route parts
    if ($directionState->get('weight') === 0 && !empty($routeState->get($stage))) {
      // Also set Direction SavedState to next stage
      $directionState->set('stage', ++$stage);
      Db::get()->flush();
      Db::get()->clear();
      return;
    }
    // 3. Directions
    $directionsList = $this->driver->getDirections($locationList, self::MAXREQUESTS);
    // 4. Encoded route
    $encodedRoute = array_map(array($this->driver, 'getPolyline'), $directionsList);
    // 5. Set data for Db and Flush it
    array_map(array(Db::get(), 'persist'), $directionsList);
    array_map(
      array($routeState, 'add')
      , array_pad(array(), count($encodedRoute), $stage)
      , $encodedRoute
    );

    $lastLocation = array_pop($locationList);
    $lastDirection = array_pop($directionsList);
    $countCheck = count($locationList) < $this->getLocationLimit();
    if ($lastDirection instanceof Direction) {
      $lastDirectionLocation = $lastDirection->destination;
      $locationCheck = $lastDirectionLocation == $lastLocation && $countCheck;
    }
    if (!isset($lastDirectionLocation) || $locationCheck) {
      $directionState->set('weight', 0);
      $directionState->set('stage', ++$stage);
    } else {
      $directionState->set('weight', $lastDirectionLocation->getWeight());
      $directionState->set('stage', $stage);
    }
    Db::get()->flush();
    Db::get()->clear();
  }

}
