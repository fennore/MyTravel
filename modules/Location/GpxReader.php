<?php

namespace MyTravel\Location;

use MyTravel\Core\Controller\Db;
use MyTravel\Core\Model\File;
use MyTravel\Location\Model\Location;

class GpxReader {
  private $file;

  public function __construct(File $file) {
    $this->file = $file;
  }
  /**
   * Writes GPX data as Location to database.
   * @param int $stage Which stage GPX locations belong to.
   */
  public function saveGpxAsLocations($stage) {
    $xml = (array) \simplexml_load_file($this->file->getFullSource(), null, LIBXML_NOCDATA);
    // Validate gpx file
    if (empty($xml['wpt']) || !is_array($xml['wpt'])) {
      return array();
    }
    $i = 0;
    // Add all GPX data as Location
    foreach ($xml['wpt'] as $coordinate) {
      $location = new Location(array(
        'lat' => $coordinate->attributes()->lat,
        'lng' => $coordinate->attributes()->lon,
        'info' => $coordinate->name,
        'status' => 1,
        'weight' => $i++,
        'stage' => $stage
      ));
      Db::get()->persist($location);
      if (($i % Db::BATCHSIZE) === 0) {
        Db::get()->flush();
      }
    }
    Db::get()->flush();
  }

}
