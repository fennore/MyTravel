<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use MyTravel\Core\OutputInterface;

class JsonOutput implements OutputInterface {
  public function output(GetResponseForControllerResultEvent $event) {

    return new JsonResponse($event->getControllerResult());
  }

}
