<?php

namespace MyTravel\Core\Controller;

use DateTime;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use MyTravel\Core\OutputInterface;

/**
 * Singleton output
 */
class OutputController {

  /**
   * OutputController singleton instance
   * @var type
   */
  private static $oc;
  private $outputHandler;

  public function __construct() {
    
  }

  public static function listen() {
    if (!(self::$oc instanceof self)) {
      self::$oc = new self();

      App::event()
        ->addListener(KernelEvents::REQUEST, array(self::$oc, 'defineOutput'));
      App::event()
        ->addListener(KernelEvents::VIEW, array(self::$oc, 'handleOutput'));
      /* App::event()
        ->addListener(KernelEvents::EXCEPTION, array(self::$oc, 'handleException')); */
    }
    return self::$oc;
  }
  /**
   * Sets the output handler according to the request
   * @param GetResponseEvent $event
   */
  public function defineOutput(GetResponseEvent $event) {
    $request = $event->getRequest();
    // check getFormat vs getRequestFormat
    // application/json as json
    if ($request->getRequestFormat() === 'json') {
      $this->outputHandler = new JsonOutput();
    }
    // application/xml as xml
    if ($request->getRequestFormat() === 'xml') {
      $this->outputHandler = new XmlOutput();
    }
    // output files as file
    // // js
    // // css
    // // img
    // text/html default output as theming
    // Note: no html output on XmlHttpRequest!
    if (!($this->outputHandler instanceof OutputInterface) && !$request->isXmlHttpRequest()) {
      $this->outputHandler = new Theming();
    }
  }

  public function handleOutput(GetResponseForControllerResultEvent $event) {
    if (!$this->checkBadRequest($event)) {
      // Check for response object
      // Update response object or create new one with content
      $output = $this
        ->outputHandler
        ->output($event);
      if (!($output instanceof Response)) {
        $response = new Response($output);
      } else {
        $response = $output;
      }
      // Set caching for json GET
      if ($event->getRequest()->getMethod() === 'GET') {
        $response
          ->setMaxAge(60 * 60 * 24) //
          ->setExpires(new DateTime('1 day'))
          ->setLastModified(new DateTime());
      }
      $event->setResponse($response);
    }
  }

  private function checkBadRequest(GetResponseForControllerResultEvent $event) {
    // Send a 400 code bad request
    if (!isset($this->outputHandler)) {
      $response = new Response();
      $response->setStatusCode(400);
      $event->setResponse($response);
      return true;
    }
    return false;
  }

  public function handleException(GetResponseForExceptionEvent $event) {
    $theming = new Theming();
    $response = new Response($theming->render('404.tpl', array()));
    $response->setStatusCode(404);
    $event->setResponse($response);
  }

}
