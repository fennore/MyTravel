<?php

namespace MyTravel\Core\Controller;

use ErrorException;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use MyTravel\Core\OutputInterface;
use MyTravel\Core\TemplateInterface;

/**
 *
 */
class Theming implements OutputInterface {

  private $themer;

  /**
   * Load the themer.
   * Adding globals to the themer:
   *  - canEdit: simple global edit access
   *  - basepath: application basepath when the website does not run on domain root
   *  - svgsprite: sprite file for svg used in theming
   */
  public function __construct() {
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
  }

  /**
   * Render twig template
   * @param string $template Template file
   * @param array $variables Variables to use in template
   * @return string
   */
  public function render($template, $variables) {
    // If array is given for template resolve
    if (is_array($template)) {
      $resolved = $this->themer->resolveTemplate($template);
      // overwrite
      $template = $resolved->getTemplateName();
    }
    return $this->themer->render($template, $variables);
  }

  /**
   * View output.
   * @param GetResponseForControllerResultEvent $event
   */
  public function output(GetResponseForControllerResultEvent $event) {
    // Load template file
    $controllerResult = $event->getControllerResult();
    if ($controllerResult instanceof TemplateInterface) {
      $template = $controllerResult->getTemplate() ?? 'default.tpl';
      $variables = $controllerResult->getVariables() ?? array();
    } else {
      throw new ErrorException('html output expects a TemplateInterface controller result');
    }
    $themedOutput = $this->render($template, $variables);

    return $themedOutput;
  }

}
