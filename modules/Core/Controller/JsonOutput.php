<?php

namespace MyTravel\Core\Controller;

use DateTime;
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
    $response = new JsonResponse($event->getControllerResult());
    // Set caching for json GET
    if ($event->getRequest()->getMethod() === 'GET') {
      $response
        ->setMaxAge(60 * 60 * 24) //
        ->setExpires(new DateTime('1 day'))
        ->setLastModified(new DateTime());
    }
    return $response;
  }

}
