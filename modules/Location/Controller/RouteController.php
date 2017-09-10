<?php

namespace MyTravel\Location\Controller;

use MyTravel\Core\Controller\Db;
use MyTravel\Core\Controller\SavedStateController;
use MyTravel\Location\GapiHelper;
use MyTravel\Location\Model\Direction;
use MyTravel\Location\Model\GapiDirectionRequest;
use Doctrine\ORM\Query\Expr;

class RouteController {

  const STATEROUTE = 874;
  const STATEDIRECTIONS = 589;

  /**
   * Maximum amount of locations sent per 1 direction request
   * @see https://developers.google.com/maps/documentation/javascript/directions#UsageLimits
   */
  const REQUESTSIZE = 25;

  /**
   * Because it's a lucky number? Nothing can ever go wrong!
   */
  const MAXREQUESTS = 13;

  public function calculateEncodedRoute() {
    // 1. Get states
    $stateCtrl = new SavedStateController();
    $directionState = $stateCtrl->get(self::STATEDIRECTIONS);
    $routeState = $stateCtrl->get(self::STATEROUTE);
    // 2. Get Locations
    $qb = Db::get()->createQueryBuilder();
    $expr = $qb->expr()->andX(
      $qb->expr()->gte('l.weight', ':weight'), $qb->expr()->eq('l.stage', ':stage')
    );
    $qb
      ->select('l')
      ->from('\MyTravel\Location\Model\Location', 'l')
      ->where($expr)
      ->setParameter(':weight', $directionState->weight ?? 0)
      ->setParameter(':stage', $directionState->stage ?? 1)
      ->orderBy('l.weight', 'ASC')
      // First request can have 25 new locations, rest 24.
      // Because we reuse the destination as origin for consecutive requests.
      ->setMaxResults((self::REQUESTSIZE - 1) * self::MAXREQUESTS + 1)
    ;
    $locationList = $qb->getQuery()->getResult();
    // 3. Directions
    $directionList = $this->getGoogleDirections($locationList);
    // 4. Encoded route
    // @todo Loop through directions, persist them, and build encodedRoute (SavedState)
    //  routes are listed per stage
    //$encodedRoutes = array_merge(array_diff_key(array_pad(array(), (int) $status->stage + 1, array()), (array) $encodedRoutes), (array) $encodedRoutes);
    
    /* $encodedRoutes = array_pad($encodedRoutes, (int) $status->stage + 1, array());
      if ($numResults < $limit) {
      $status->weight = 0;
      $status->stage++;
      } else {
      $status->weight = $destination['weight'];
      }

      $placeholders = array_fill(0, count($insertValues) / $countColumns, implode(',', array_fill(0, $countColumns, '?')));
      $this->multiInsert($insertValues, $placeholders, $db); */

    // 5. Flush?
  }

  public function getGoogleDirections($locationList) {
    $directionList = array();
    // Stop execution when not enough waypoints are available
    if (empty($locationList) || count($locationList) < 2) {
      return $directionList;
    }
    // Set first origin
    $origin = array_shift($locationList);
    $setBack = 0;
    while (!empty($locationList)) {

      $modes = GapiHelper::DIRECTIONMODES;
      // Note: the origin must always be the same for every travel mode
      do {
        $modeSet = array_shift($modes);
        $mode = key($modeSet);
        $size = (current($modeSet) ?? self::REQUESTSIZE) - $setBack;

        $listChunk = array_slice($locationList, 0, $size);
        $destination = array_pop($listChunk);
        $directionRequest = new GapiDirectionRequest();
        array_map(array($directionRequest, 'addWaypoint'), $listChunk);
        $response = $directionRequest
          ->setMode($mode)
          ->setOrigin($origin)
          ->setDestination($destination)
          ->setAvoid('ferries|tolls|highways')
          ->getDirections();
      } while (empty($response->routes) && !empty($modes));

      array_splice($locationList, 0, $size);
      // Set next origin as last destination
      $origin = $destination;
      // From now on we reuse one location so we need one less from locationList
      $setBack = 1;
      // Prepare all values
      // @todo https://stackoverflow.com/questions/16180104/get-a-polyline-from-google-maps-directions-v3
      if (!empty($response->routes)) {
        $direction = new Direction();
        $direction
          ->setOrigin($origin)
          ->setDestination($destination)
          ->setStage($origin->getStage())
          ->setData($response);
        array_push($directionList, $direction);
      }
    }
    return $directionList;
  }

}
