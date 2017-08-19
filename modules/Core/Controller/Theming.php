<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class Theming {
  private $themer;

  public function load() {
    // get theme from config
    $themingDirectory = Config::get()->directories['views'] . '/' . Config::get()->view;
    $loader = new FilesystemLoader($themingDirectory);
    $this->themer = new Environment($loader);
    App::event()
      ->addListener(KernelEvents::VIEW, array($this, 'view'));
  }

  public function view(GetResponseForControllerResultEvent $event) {
    // load template file
    $template = $event->getControllerResult()->getTemplate() ?? 'default.tpl';
    $variables = $event->getControllerResult()->getVariables() ?? array();
    $themedOutput = $this->themer->render($template, $variables);
    // Set response
    $event->setResponse(new Response($themedOutput));
  }

}
