<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

/**
 *
 */
class Theming {
  private $themer;

  /**
   * Load the themer.
   * Adding globals to the themer:
   *  - canEdit: simple global edit access
   *  - basepath: application basepath when the website does not run on domain root
   *  - svgsprite: sprite file for svg used in theming
   */
  public function load() {
    // get theme from config
    $themingDirectory = Config::get()->directories['views'] . '/' . Config::get()->view;
    $svgSpritePath = $themingDirectory . '/img/sprite.svg';
    $loader = new FilesystemLoader($themingDirectory);
    $this->themer = new Environment($loader);
    // Add global variables
    $this->themer->addGlobal('canEdit', App::get()->hasAccess());
    $this->themer->addGlobal('basepath', App::get()->basePath());
    if (\file_exists($svgSpritePath)) {
      $this->themer->addGlobal('svgsprite', \file_get_contents($svgSpritePath));
    }
    App::event()
      ->addListener(KernelEvents::VIEW, array($this, 'view'));
  }

  /**
   * Render a view.
   * @param GetResponseForControllerResultEvent $event
   */
  public function view(GetResponseForControllerResultEvent $event) {
    // Load template file
    $template = $event->getControllerResult()->getTemplate() ?? 'default.tpl';
    $variables = $event->getControllerResult()->getVariables() ?? array();
    $themedOutput = $this->themer->render($template, $variables);
    // Set response
    $event->setResponse(new Response($themedOutput));
  }

}
