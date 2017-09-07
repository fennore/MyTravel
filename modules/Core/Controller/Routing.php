<?php

namespace MyTravel\Core\Controller;

use Throwable;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use MyTravel\Core\ServiceFactoryInterface;
use MyTravel\Core\Event\RoutingEvent;
use MyTravel\Core\CoreEvents;
use MyTravel\Core\Model\Module;

/**
 * Singleton service factory for routing
 */
final class Routing implements ServiceFactoryInterface {

  /**
   * Being itself
   * @var MyTravel\Core\Controller\Routing
   */
  protected static $routing;
  /**
   * @var Symfony\Component\Routing\RouteCollection
   */
  private $routes;
  private $pathGenerator;
  private $routeGenerator;

  protected function __construct() {
    
  }

  /**
   * Short alias for App::service()->get('routes')
   * @return self
   */
  public static function get() {
    return App::service()->get('routing');
  }

  public function path($name, $params = array()) {
    return $this->pathGenerator->generate($name, $params);
  }

  public function routePath($name, $params = array()) {
    return trim($this->routeGenerator->generate($name, $params), '/');
  }

  /**
   * Set as routing service
   * @return self
   */
  public static function setService() {
    if (!(self::$routing instanceof self)) {
      self::$routing = new self();
    }
    return self::$routing;
  }
  /**
   * Build routing
   */
  public function build() {
    // Get possible route file directories
    $modules = Modules::get()->all();
    $directories = array_map(array($this, 'getModuleRouteFileDirectory'), $modules);
    // Load Core routes from file
    $locator = new FileLocator('./modules/Core');
    $loader = new YamlFileLoader($locator);
    $this->routes = $loader->load('routes.yml');
    // Add modules routes from file
    array_map(array($this, 'concatRoute'), $directories);
    // Dispatch event for altering application directories config node
    $event = new RoutingEvent($this->routes);
    App::event()->dispatch(CoreEvents::BUILDROUTES, $event);
  }

  public function getModuleRouteFileDirectory(Module $module) {
    if ($module->isActive()) {
      return './modules/' . $module->name;
    }
  }

  private function concatRoute($directory) {
    try {
      $locator = new FileLocator($directory);
      $loader = new YamlFileLoader($locator);
      $collection = $loader->load('routes.yml');
      $this->routes->addCollection($collection);
    } catch (FileLocatorFileNotFoundException $ex) {
      // No file found is OK, just @todo set a notification for admin
    } catch (Throwable $throwMe) {
      throw $throwMe;
    }
  }

  private function setUrlGenerators() {
    $context = new RequestContext();
    $context->fromRequest(App::get()->getRequest());
    $this->pathGenerator = new UrlGenerator($this->routes(), $context);
    $context = clone $context;
    $context->setBaseUrl('');
    $this->routeGenerator = new UrlGenerator($this->routes(), $context);
  }

  /**
   * Update routing from config
   */
  public function update() {
    $routesConfig = Config::get()->routing['routes'];
    foreach ($routesConfig as $name => $config) {
      if ($name === 'home' && !empty($config['callback'])) {
        $defaults = $this->routes
          ->get($name)
          ->getDefaults();
        $defaults['callback'] = $config['callback'];
        $this->routes
          ->get($name)
          ->setDefaults($defaults);
      } else if (isset($config['path'])) {
        $this->routes
          ->get($name)
          ->setPath($config['path']);
      }
    }
    // Now that we have proper routes, set url generators
    $this->setUrlGenerators();
  }

  /**
   * Get the route collection
   * @return Symfony\Component\Routing\RouteCollection
   */
  public function routes() {
    return $this->routes;
  }

}
