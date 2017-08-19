<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

class Theming {

  public function load() {
    App::event()
      ->addListener(KernelEvents::VIEW, array($this, 'view'));
  }

  public function view(GetResponseForControllerResultEvent $event) {
    // get theme from config
    // load template file from right theme
    // Set response
    $event->setResponse(new Response('Theming response ' . $event->getControllerResult()['page']));
  }

}
