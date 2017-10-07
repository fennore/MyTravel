<?php

namespace MyTravel\Core\Controller;

use ErrorException;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\Packages;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;
use MyTravel\Core\OutputInterface;
use MyTravel\Core\TemplateInterface;
use MyTravel\Core\CoreEvents;
use MyTravel\Core\Event\ThemingEvent;

/**
 *
 */
class Theming implements OutputInterface {
  /**
   *
   * @var Twig\Environment
   */
  private $themer;

  /**
   *
   * @var string
   */
  private $themeDirectory;
  private $assets;

  /**
   * Load the themer.
   * Adding globals to the themer:
   *  - canEdit: simple global edit access
   *  - basepath: application basepath when the website does not run on domain root
   *  - svgsprite: sprite file for svg used in theming
   */
  public function __construct() {
    // get theme from config
    $this->themeDirectory = Config::get()->directories['views'] . '/' . Config::get()->view;
    $loader = new FilesystemLoader($this->themeDirectory);
    $this->themer = new Environment($loader);
    // Setup Assets
    $this->setAssets();
    // Add global variables
    $this->addGlobals();
    $this->addFunctions();
    // Load themer event
    $event = new ThemingEvent($this->themer);
    App::event()->dispatch(CoreEvents::THEMERLOAD, $event);
  }

  private function setAssets() {
    // CDN list
    $cdnList = array(
      'https://unpkg.com/'
    );
    // Context
    $stack = new RequestStack();
    $stack->push(App::get()->getRequest());
    $context = new RequestStackContext($stack);
    // Default package
    $defaultPackage = new PathPackage($this->themeDirectory, new EmptyVersionStrategy(), $context);
    // Named packages
    $namedPackages = array(
      'cdn' => new UrlPackage($cdnList, new EmptyVersionStrategy()),
      'local' => $defaultPackage,
    );

    $this->assets = new Packages($defaultPackage, $namedPackages);
  }

  private function addGlobals() {
    $svgSpritePath = $this->themeDirectory . '/img/sprite.svg';
    $this->themer->addGlobal('canEdit', App::get()->hasAccess());
    $this->themer->addGlobal('basepath', App::get()->basePath());
    if (\file_exists($svgSpritePath)) {
      $this->themer->addGlobal('svgsprite', \file_get_contents($svgSpritePath));
    }
  }

  private function addFunctions() {
    $this->themer->addFunction(new TwigFunction('path', array(Routing::get(), 'path')));
    $this->themer->addFunction(new TwigFunction('asset', array($this->assets, 'getUrl')));
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
