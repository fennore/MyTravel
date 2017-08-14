<?php

namespace MyTravel\Core\Controller;

class XmlOutput implements OutputInterface {
  public function output() {
    $document = new \DOMDocument("1.0", 'UTF-8');
    $document->formatOutput = true;
  }
  /**
   *
   * @param type $gpxList
   * @param type $filename
   * @return \DOMDocument
   */
  public function outputGpx($gpxList, $filename) {
    $document = new \DOMDocument("1.0", 'UTF-8');
    $document->formatOutput = true;
    $gpx = $document->createElementNS("http://www.topografix.com/GPX/1/0", "gpx");
    $gpx->setAttribute("version", "1.0");
    // @todo no fixed data
    $gpx->setAttribute("creator", "Mytravel");
    $gpx->setAttributeNS(
      'http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd'
    );

    foreach ($gpxList as $loc) {
      $wpt = $document->createElement('wpt');
      $wpt->setAttribute("lat", (float) $loc->lat);
      $wpt->setAttribute("lon", (float) $loc->lng);
      $name = $document->createElement('name', $loc->info);
      $wpt->appendChild($name);
      $gpx->appendChild($wpt);
    }

    $document->appendChild($gpx);
    if (isset($filename)) {
      // sprintf('%02d', $stage) . '-stage
      $document->save(App::$dirLoc . '-backup/' . $filename . '.gpx');
    } else {
      return $document;
    }
  }

}
