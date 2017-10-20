<?php

namespace MyTravel\Core\Output;

use DOMDocument;
use DOMElement;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Service\Config;

class XmlOutput implements OutputInterface {

  private $document;

  public function __construct() {
    $this->document = new DOMDocument("1.0", 'UTF-8');
    $this->document->formatOutput = true;
  }

  public function output(GetResponseForControllerResultEvent $event) {
    
    /**
     * getChildren
     * => loop
     * => $document->appendChild($xml);
     */
    return $this->document;
  }

  /**
   * Save an XML Document in the files directory
   * @param DOMElement $childDomTree
   * @param string $fileLoc location under files directory
   */
  public function saveFile(DOMElement $childDomTree, $fileLoc) {
    /**
     * getChildren
     * => loop
     * => $document->appendChild($xml);
     */
    // sprintf('%02d', $stage) . '-stage
    $this->document->save(Config::get()->directories['files'] . '/' . $fileLoc);
  }

  /**
   * @todo pretty much everything, this is just some old code floating around
   * @param type $gpxList
   * @param type $filename
   * @return \DOMDocument
   */
  private function outputGpx(GetResponseForControllerResultEvent $event) {
    $gpxlist;
    $filename;
    $document = new DOMDocument("1.0", 'UTF-8');
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
