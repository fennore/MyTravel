<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use MyTravel\Core\OutputInterface;

class JsonOutput implements OutputInterface {

  /**
   * Output for json request
   * @param GetResponseForControllerResultEvent $event
   * @return JsonResponse
   */
  public function output(GetResponseForControllerResultEvent $event) {
    // Set response object
    return new JsonResponse($event->getControllerResult());
  }

}
