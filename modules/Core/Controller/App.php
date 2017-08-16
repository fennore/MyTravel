<?php

namespace MyTravel\Core\Controller;

use Throwable;
use ErrorException;

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

  protected function __construct() {

  }

  /**
   * Interact with the application.
   * This simply returns the application object.
   * @return App
   */
  public static function get() {
    return self::$app;
  }
  /**
   * Load the application.
   * This has to be called before doing anything else that
   * concerns the application object.
   * @return self
   */
  public static function load() {
    if (!(self::$app instanceof App)) {
      self::$app = new App();
    }
    return self::$app;
  }
  /**
   * Build the application.
   * This loads all modules,
   * and dispatches events.
   * @todo support composer (set composer as autoloader?)
   * @return $this
   * @throws ErrorException
   */
  public function build() {
    try {
      if (empty($this->autoloader)) {
        throw new ErrorException('No proper autoloader has been set. This is required before building.');
      }
      // Load Modules
      ModuleController::getModules();
      // Load Routing
    //
    } catch (Throwable $ex) {
      /** @todo add message to php-error list */
      throw $ex;
    }

    return $this;
  }

  /**
   * Autoloading classes in absence of composer.
   * Requires file path and callback.
   * @todo Check for using class loader caching(xcache, apc, wincache, ...)
   * @param string $autoloaderPath Required. Path to the file containing the autoloader
   * @param callback $callback Required.
   */
  public function setAutoloader($autoloaderPath, $instanceCall) {
    // Uses
    require_once './' . $autoloaderPath;
    $this->autoloader = new $instanceCall();
    // Support method chaining
    return $this;
  }

  /**
   * Set prefixes for autoloading classes without composer.
   * @param callback $callback Required
   * @param array $prefixes
   */
  public function setAutoloadPrefixes($callback, $newPrefixes = array()) {
    // Set callback
    $this->cbalPrefix = $callback;
    // Add prefixes for default MyTravel
    $prefixes = array_merge(
      array(
        array('MyTravel\\Core', 'modules\\Core'),
        array('Symfony\\Component', 'lib')
      ), $newPrefixes
    );
    foreach ($prefixes as $prefix) {
      $this->addAutoloadPrefix($prefix[0], $prefix[1]);
    }
    // Support method chaining
    return $this;
  }
  /**
   * Add more prefixes for sources to the autoloader.
   * This is used for example by modules to register themselves.
   * @param type $prefix
   * @param type $source
   * @return $this
   */
  public function addAutoloadPrefix($prefix, $source) {
    call_user_func_array(array($this->autoloader, $this->cbalPrefix), array($prefix, $source));
    return $this;
  }

  /**
   * Register the autoloader with the php built-in autoloading
   * @param callback $callback Required. The register method of the autoloader.
   * @param array $args Array of arguments for the autoloader register callback.
   * @return $this
   */
  public function registerAutoloader($callback, $args = array()) {
    call_user_func_array(array($this->autoloader, $callback), $args);
    // Support method chaining
    return $this;
  }

  /**
   * Check if application is considered to be in development environment
   * @return boolean
   */
  public function inDevelopment() {
    return Config::get()->environment === 'dev';
  }

  /**
   * Get the basepath.
   * Pretty much redundant. A joke!
   * Just use Config::get()->basepath instead.
   * @return string
   */
  public function basePath() {
    return Config::get()->basepath;
  }

}
