<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use MyTravel\Core\ServiceFactoryInterface;
use MyTravel\Core\Event\RoutingEvent;

/**
 * Singleton service factory for routing
 */
class Routing implements ServiceFactoryInterface {
  /**
   * Being itself
   * @var MyTravel\Core\Controller\Routing
   */
  protected static $routing;
  /**
   * @var Symfony\Component\Routing\RouteCollection
   */
  private $routes;

  protected function __construct() {
    
  }

  /**
   * Short alias for App::service()->get('routes')
   * @return self
   */
  public static function get() {
    return App::service()->get('routing');
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
    // Load routes from yml file
    $locator = new FileLocator(array('./modules/Core'));
    $loader = new YamlFileLoader($locator);
    $this->routes = $loader->load('routes.yml');

    // Dispatch event for altering application directories config node
    $event = new RoutingEvent($this->routes);
    App::event()->dispatch('module.routing.routes.build', $event);
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
  }

  public function routes() {
    return $this->routes;
  }

}
