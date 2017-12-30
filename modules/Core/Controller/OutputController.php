<?php

namespace MyTravel\Core\Controller;

use DateTime;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use MyTravel\Core\Service\Config;
use MyTravel\Core\OutputInterface;
use MyTravel\Core\Output\Css;
use MyTravel\Core\Output\Js;
use MyTravel\Core\Output\FileOutput;
use MyTravel\Core\Output\XmlOutput;
use MyTravel\Core\Output\JsonOutput;
use MyTravel\Core\Output\Theming;

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
      /** single responsibility, output controller does not do access checking
      App::event()
        ->addListener(KernelEvents::REQUEST, array(self::$oc, 'checkAccess'));*/
      App::event()
        ->addListener(KernelEvents::CONTROLLER, array(self::$oc, 'defineOutput'));
      App::event()
        ->addListener(KernelEvents::VIEW, array(self::$oc, 'handleOutput'));
      if(!App::get()->inDevelopment()) {
        App::event()
          ->addListener(KernelEvents::EXCEPTION, array(self::$oc, 'handleException'));
      }
    }
    return self::$oc;
  }
  
  /**
   * Checks request access and returns 403 if needed.
   * Used as KernelEvents listener callback.
   * @param GetResponseEvent $event
   * @deprecated
   */
  public function checkAccess(GetResponseEvent $event) {
    if (!App::get()->hasAccess()) {
      $theming = new Theming();
      $response = new Response($theming->render('403.tpl', array()));
      $response->setStatusCode(403);
      $event->setResponse($response);
    }
  }
  
  /**
   * Sets the output handler according to the request.
   * Used as KernelEvents listener callback.
   * @param GetResponseEvent $event
   */
  public function defineOutput(FilterControllerEvent $event) {
    // Check if controller is of OutputInterface and if so set it as such
    $controller = $event->getController();
    if($controller[0] instanceof OutputInterface) {
      $this->outputHandler = $controller[0];
      return; // Shortcut
    }
    $request = $event->getRequest();
    // @todo Check getFormat vs getRequestFormat
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
    if ($request->getRequestFormat() === 'application/javascript') {
      $this->outputHandler = new Js();
    }
    // // css
    if ($request->getRequestFormat() === 'text/css') {
      $this->outputHandler = new Css();
    }
    // // img
    $fileFormats = array(
      'image/*'
    );
    if (in_array($request->getRequestFormat(), $fileFormats)) {
      $this->outputHandler = new FileOutput($request);
    }
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
      // Set caching for GET.
      // It's dumb caching.
      // But dumb caching rocks because it's fast!
      // Especially with proper CDN in-between to cache once for all.
      $methodCheck = $event->getRequest()->getMethod() === 'GET';
      $lastModCheck = empty($response->getLastModified());
      if ($methodCheck) {
        $seconds = Config::get()->pagecachetime;
        $response
          /**
           * Set max age as shared (public) for CDN support
           */
          ->setSharedMaxAge($seconds) //
          ->setExpires(new DateTime('+' . $seconds . ' seconds'));
      }
      if ($methodCheck && $lastModCheck) {
        $response->setLastModified(new DateTime());
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
