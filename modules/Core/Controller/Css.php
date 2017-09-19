<?php

namespace MyTravel\Core\Controller;

use MyTravel\Core\OutputInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class Css implements OutputInterface {

  public function output(GetResponseForControllerResultEvent $event) {
    
  }

}
