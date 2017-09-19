<?php

namespace MyTravel\Core\Controller;

use Patchwork\JSqueeze;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use MyTravel\Core\OutputInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class Js implements OutputInterface {

  public function viewJsBundle(Request $request) {
    $sq = new JSqueeze();
    $minifiedJs = array();
    // Fetch all js files from view
    $dirFiles = Finder::create()
      ->files()
      ->in(Config::get()->directories['views'] . '/' . Config::get()->view . '/js')
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
