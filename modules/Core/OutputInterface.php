<?php

namespace MyTravel\Core;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

interface OutputInterface {

  public function output(GetResponseForControllerResultEvent $event);
}
