<?php

namespace MyTravel\Core\Controller;

use DateTime;
use Throwable;
use ErrorException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * Singleton Application controller for setting up the application.
 */
class App {
  /**
   * App instance
   * @var App
   */
  protected static $app;

  /**
   * Autoloader object
   * @var object
   */
  private $autoloader;

  /**
   * Autoloader callback to set prefixes
   * @var callback
   */
  private $cbalPrefix;

  /**
   * Service container for the application
   * @var Symfony\Component\DependencyInjection\ContainerBuilder
   */
  private $serviceContainer;

  /**
   * The Request object
   * @var Symfony\Component\HttpFoundation\Request
   */
  private $request;

  protected function __construct() {}

  /**
   * Interact with the application.
   * This simply returns the application object.
   * @return App
   */
  public static function get() {
    return self::$app;
  }

  /**
   * Get the service container
   * @return Symfony\Component\DependencyInjection\ContainerBuilder
   */
  public static function service() {
    return self::$app->serviceContainer;
  }

  /**
   * Short alias for App::service()->get('events')
   * @return Symfony\Component\EventDispatcher\EventDispatcher
   */
  public static function event() {
    return self::service()->get('events');
  }

  /**
   * Load the application.
   * This has to be called before doing anything else that
   * concerns the application object.
   * @return self
   */
  public static function load() {
    if (!(self::$app instanceof self)) {
      self::$app = new self();
    }
    return self::$app;
  }

  /**
   * Build the application.
   * This loads all modules, and dispatches events.
   * @todo support composer (set composer as autoloader?)
   * @todo split config between db / app / route
   * => load db config as first (for possible module config in db)
   * => load module config (status / active / event weights)
   * => then load modules
   * => load app config
   * => load routing
   * => update routing
   * @return App
   * @throws ErrorException
   */
  public function build() {
    try {
      if (empty($this->autoloader)) {
        throw new ErrorException('No proper autoloader has been set. This is required before building.');
      }
      // Set service container
      $this->serviceContainer = new ContainerBuilder();
      // Set config service and load basic config
      $this->serviceContainer
        ->register('config')
        ->setFactory('MyTravel\Core\Controller\Config::setService');
      // Set request or shortcut with page cache
      $this->setRequest();
      // Register all other services
      $this->registerServices();
      // Load Modules
      // => this needs to be done first after setting services
      // - this will also load module config
      $this->serviceContainer
        ->get('modules')
        ->load();
      // Build Routing
      // - routing is build by modules
      //  => routing needs to be loaded after loading modules
      $this->serviceContainer
        ->get('routing')
        ->build();
      // After routing is built we can add the config for it
      $this->serviceContainer
        ->get('config')
        ->addRoutingConfig();
      // Update Routing from config
      $this->serviceContainer
        ->get('routing')
        ->update();
      // Db config
      $this->serviceContainer
        ->get('config')
        ->addDatabaseConfig();
      // Connect to db
      $this->serviceContainer
        ->get('db')
        ->connect();
      // Directory config
      $this->serviceContainer
        ->get('config')
        ->addDirectoryConfig();
      // Initialize modules
      // - runs init on all active modules
      $this->serviceContainer
        ->get('modules')
        ->init();
      //
    } catch (Throwable $ex) {
      /** @todo add message to php-error list */
      if ($this->inDevelopment()) {
        var_dump($ex->getTrace());
      } // *** You haven't seen this ***
      throw $ex;
    }

    return $this;
  }

  /**
   * Handles the application output.
   * @todo check if and where caching can be improved.
   *
   * @return App
   */
  public function output() {
    // Add output listeners
    $oc = new OutputController();
    $oc->listen();
    // Create matcher
    $matcher = new UrlMatcher(Routing::get()->routes(), new RequestContext());
    // Subscribe a route listener to the events
    $this->serviceContainer->get('events')
      ->addSubscriber(new RouterListener($matcher, new RequestStack()));
    // Create kernel object
    $kernel = new HttpKernel(
      $this->serviceContainer->get('events'), new ControllerResolver(), new RequestStack(), new ArgumentResolver()
    );

    $response = $kernel->handle($this->getRequest());
    $response->send();

    $kernel->terminate($this->getRequest(), $response);
    // Execute any queries left behind
    Db::flushAll();

    return $this;
  }

  /**
   * Make application services directly available through the application from anywhere.
   * This loads all services after the config service.
   * Use App::service() to access any service,
   * or use App::event() as alias to access the events service.
   */
  private function registerServices() {
    // Modules
    $this->serviceContainer
      ->register('modules')
      ->setFactory('MyTravel\Core\Controller\Modules::setService');
    // Routing
    $this->serviceContainer
      ->register('routing')
      ->setFactory('MyTravel\Core\Controller\Routing::setService');
    // Event dispatcher
    $this->serviceContainer
      ->register('events', 'Symfony\Component\EventDispatcher\EventDispatcher');
    // Database
    $this->serviceContainer
      ->register('db')
      ->setFactory('MyTravel\Core\Controller\Db::setService');
  }

  /**
   * Autoloading classes in absence of composer.
   * Requires file path and callback.
   * @todo Check for using class loader caching(xcache, apc, wincache, ...)
   * @param string $autoloaderPath Required. Path to the file containing the autoloader
   * @param callback $callback Required.
   * @return App
   */
  public function setAutoloader($id, $autoloaderPath, $instanceCall) {
    // Uses
    require_once './' . $autoloaderPath;
    $this->autoloader[$id] = new $instanceCall();
    // Support method chaining
    return $this;
  }

  /**
   * Set prefixes for autoloading classes without composer.
   * @param callback $callback Required
   * @param array $prefixes
   * @return App
   */
  public function setAutoloadPrefixes($id, $callback, $newPrefixes = array()) {
    // Set callback
    $this->cbalPrefix[$id] = $callback;
    // Add prefixes for default MyTravel
    $prefixes = array_merge(
      array(
        array('MyTravel\Core', 'modules/Core'),
        array('Symfony\Component', 'lib'),
        array('Psr\Container', 'lib/Psr/src')
      ), $newPrefixes
    );
    foreach ($prefixes as $prefix) {
      $this->addAutoloadPrefix($id, $prefix[0], $prefix[1]);
    }
    // Support method chaining
    return $this;
  }

  /**
   * Add more prefixes for sources to the autoloader.
   * This is used for example by modules to register themselves.
   * @param type $prefix
   * @param type $source
   * @return App
   */
  public function addAutoloadPrefix($id, $prefix, $source) {
    call_user_func_array(array($this->autoloader[$id], $this->cbalPrefix[$id]), array($prefix, $source));
    return $this;
  }

  /**
   * Register the autoloader with the php built-in autoloading
   * @param callback $callback Required. The register method of the autoloader.
   * @param array $args Array of arguments for the autoloader register callback.
   * @return $this
   */
  public function registerAutoloader($id, $callback, $args = array()) {
    call_user_func_array(array($this->autoloader[$id], $callback), $args);
    // Support method chaining
    return $this;
  }

  /**
   * Check if application is considered to be in development environment
   * @todo properly handle possible error from mistake in config => show that mistake
   * instead of fatalling out incomprehensibly
   * @return boolean
   */
  public function inDevelopment() {
    try {
      return Config::get()->environment === 'dev';
    } catch (Throwable $e) {
      return false;
    }
  }

  /**
   * Set the request.
   * Checks for last modified to return 304 and stop processing,
   * returning cache instead.
   * @todo See if we or how we can best implement
   *       specific last modified checks for files, pages etc.
   * @todo Fix config loading so we can overload it.
   *       Then we can check for dev environment to disable lastmodified check.
   *       Then overload the cache by force after modules have loaded.
   */
  private function setRequest() {
    // Create request object
    $this->request = Request::createFromGlobals();
    // Check caching and exit if so
    // - create a dummy response for possible 304
    $response = new Response();
    $response->setLastModified(new DateTime('-1 day'));
    if ($response->isNotModified($this->getRequest())) {
      $response->setPublic();
      $response->setSharedMaxAge(60 * 60 * 24);
      $response->send();
      exit();
    }
    // Add better json request support
    // check request Content-Type
    $ctCheck = 0 === strpos(
        $this->request->headers->get('CONTENT_TYPE')
        , 'application/json'
    );
    // check request Method
    $methodCheck = in_array(
      strtoupper($this->request->server->get('REQUEST_METHOD', 'GET'))
      , array('PUT', 'DELETE', 'POST')
    );
    if ($ctCheck && $methodCheck) {
      $params = (array) json_decode($this->request->getContent());
      $this->request->request = new ParameterBag($params);
    }
  }

  /**
   * Get the request object.
   * @return Symfony\Component\HttpFoundation\Request
   */
  public function getRequest() {
    return $this->request;
  }

  /**
   * Get the base path for the application.
   * This is needed for when your app runs on a subdirectory of a domain.
   * @return string
   */
  public function basePath() {
    return $this
        ->getRequest()
        ->getBasePath();
  }

  /**
   * Get a clean path version of a string.
   * @todo find something better (Symfony has path / url function?)
   * @param string $str
   */
  public function cleanPathString($str, $replace = array(), $delimiter = '-') {
    setlocale(LC_ALL, 'en_US.UTF8');

    if (!empty($replace)) {
      $str = str_replace((array) $replace, ' ', $str);
    }

    //$str = preg_replace(array('/�/', '/�/', '/�/', '/�/', '/�/', '/�/'), array('Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue'), $str);
    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
    $clean = strtolower(trim($clean, '-'));
    $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

    return $clean;
  }

  /**
   * @todo use an authentication service (Symfony)
   * This should not stay within App but move to some kind of User / Auth controller
   * @param type $key
   * @param type $access
   * @param type $user
   * @return boolean
   */
  public function hasAccess($key = 'default', $access = array(), $user = null) {
    return Config::get()->environment === 'dev';
  }

}
