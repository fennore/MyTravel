<?php

namespace MyTravel\Core\Output;

use MatthiasMullie\Minify\CSS as minifyCss;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use MyTravel\Core\OutputInterface;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Service\Config;

class Css implements OutputInterface {

  public function viewCssBundle(Request $request) {
    $minify = new minifyCss();
    $css = array();
    // Fetch all js files from view
    $dirFiles = Finder::create()
      ->files()
      ->in(Config::get()->directories['views'] . '/' . Config::get()->view . '/css')
      ->name('*.css');

    foreach ($dirFiles as $splFile) {

      if (App::get()->inDevelopment()) {
        array_push($css, $splFile->getContents() . PHP_EOL);
      } else {
        $minify->add($splFile->getContents());
      }
    }

    if (App::get()->inDevelopment()) {
      return implode('', $css);
    } else {
      return $minify->minify();
    }
  }

  public function output(GetResponseForControllerResultEvent $event) {
    $response = new Response($event->getControllerResult());
    $response->headers->set('Content-Type', 'text/css');
    return $response;
  }

}
