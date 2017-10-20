<?php

namespace MyTravel\Core\Output;

use Patchwork\JSqueeze;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use MyTravel\Core\OutputInterface;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Service\Config;

class Js implements OutputInterface {

  public function viewJsBundle(Request $request) {
    $sq = new JSqueeze();
    $minifiedJs = array();
    // Fetch all js files from view
    $dirFiles = Finder::create()
      ->files()
      ->in(Config::get()->directories['views'] . '/' . Config::get()->view . '/js')
      ->sortByType() // Force sorting, required for linux
      ->name('*.js');

    foreach ($dirFiles as $splFile) {

      if (App::get()->inDevelopment()) {
        array_push($minifiedJs, $splFile->getContents() . PHP_EOL);
      } else {
        array_push($minifiedJs, $sq->squeeze($splFile->getContents()));
      }
    }

    return implode('', $minifiedJs);
  }

  public function output(GetResponseForControllerResultEvent $event) {
    $response = new Response($event->getControllerResult());
    $response->headers->set('Content-Type', 'application/javascript');
    return $response;
  }

}
