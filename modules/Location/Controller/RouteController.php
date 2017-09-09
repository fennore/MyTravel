<?php

namespace MyTravel\Location\Controller;

class RouteController {

  const STATEROUTE = 874;
  const STATEDIRECTIONS = 589;

  public function getGoogleDirections(&$status, &$encodedRoutes) {
    $insertValues = array();

    $db = 'direction';
    $countColumns = count(DbInfoMytravel::$dbTables[$db]['cols']);

    //handle routes-status file
    $limit = self::CHUNKSIZE * self::MAXREQUESTS;
    $query = 'SELECT * FROM location WHERE weight >= ? AND stage = ? ORDER BY weight ASC LIMIT ?';
    $params = array(
      (int) $status->weight,
      (int) $status->stage,
      $limit
    );
    $sth = $this->dbHandler->prepare($query);
    $sth->execute($params);
    $wpts = $sth->fetchAll(PDO::FETCH_ASSOC);
    $numResults = count($wpts);

    // Stop execution when no new waypoints are available
    if (empty($wpts) || $numResults < 2) {
      $status->weight = 0;
      $status->stage++;
      return;
    }

    //$encodedRoutes = array_merge(array_diff_key(array_pad(array(), (int) $status->stage + 1, array()), (array) $encodedRoutes), (array) $encodedRoutes);
    $encodedRoutes = array_pad($encodedRoutes, (int) $status->stage + 1, array());

    while (!empty($wpts)) {

      $modes = array(
        'bicycling', // 24
        'bicycling', // 6
        'walking',
        'driving'
      );
      // The origin must always be the same for every travel mode
      if (empty($destination)) {
        $origin = array_shift($wpts);
      } else {
        $origin = $destination;
      }
      // Reset request waypoints size
      $size = self::CHUNKSIZE;
      do {
        $mode = array_shift($modes);

        if (count($wpts) === $size + 1 && empty($destination)) {
          --$size;
        }

        $coordinates = array_slice($wpts, 0, $size);

        $destination = array_pop($coordinates);
        // @todo use coordinate as object with class, and let it have __tostring function for this (also tojson etc.)
        $coordinates = array_map(function($coordinate) {
          return $coordinate['lat'] . ',' . $coordinate['lng'];
        }, $coordinates);

        $arrQuery = array(
          'mode' => $mode,
          'origin' => (float) $origin['lat'] . ',' . (float) $origin['lng'], // urlencode('51.05423,3.66161')
          'destination' => (float) $destination['lat'] . ',' . (float) $destination['lng'],
          'waypoints' => 'via:' . (implode('|via:', $coordinates)), //$polylineEncoder->encodedString()
          'avoid' => 'ferries|tolls|highways',
          'key' => App::GAPIkey,
        );

        $url = App::GAPIurl . '?' . http_build_query($arrQuery);
        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_URL => $url,
          CURLOPT_SSL_VERIFYPEER => false, // ssl verification seems to fail
        ));

        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        $respObj = json_decode($resp);
        // Close request to clear up some resources
        curl_close($curl);
        // Set request size smaller as we did not get the desired first request
        if (empty($respObj->routes)) {
          $size = 6; // 5 + 1
        }
        usleep(100);
      } while (empty($respObj->routes) && !empty($modes));

      array_splice($wpts, 0, $size);

      // Prepare all values
      //@todo https://stackoverflow.com/questions/16180104/get-a-polyline-from-google-maps-directions-v3
      if (!empty($respObj->routes)) {
        $insertValues[] = (int) $status->stage; //stage
        $insertValues[] = (float) $origin['id']; //origin = location id
        $insertValues[] = (float) $destination['id']; //destination = location id
        $insertValues[] = (string) $resp; //blob = direction response
        array_push($encodedRoutes[(int) $status->stage], (string) $respObj->routes[0]->overview_polyline->points);
      }
    }

    if ($numResults < $limit) {
      $status->weight = 0;
      $status->stage++;
    } else {
      $status->weight = $destination['weight'];
    }

    $placeholders = array_fill(0, count($insertValues) / $countColumns, implode(',', array_fill(0, $countColumns, '?')));
    $this->multiInsert($insertValues, $placeholders, $db);
  }

}
