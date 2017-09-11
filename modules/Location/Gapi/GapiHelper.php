<?php

namespace MyTravel\Location\Gapi;

class GapiHelper {

  const VALIDDIRECTIONMODES = array(
    'bicycling',
    'walking',
    'driving'
  );

  /**
   * Direction modes to query with fallbacks and sizes.
   * If no size is given default will be used.
   */
  const DIRECTIONMODES = array(
    array('bicycling' => null),
    array('bicycling' => 5),
    array('walking' => 5),
    array('driving' => 5)
  );

  /**
   * Google Directions API request url with json response
   */
  const DIRECTIONSREQUESTURL = 'https://maps.googleapis.com/maps/api/directions/json';

}
