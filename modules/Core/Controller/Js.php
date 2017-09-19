<?php

namespace MyTravel\Core\Controller;

use Patchwork\JSqueeze;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class Js {

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
    $response = new Response(implode('', $minifiedJs));
    $response->headers->set('Content-Type', 'application/javascript');
    return $response;
  }

}
