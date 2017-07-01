<?php

namespace MyTravel\Core\Controller;

/**
 * Singleton App controller for setting up the application.
 */
class App {

  protected static $app;
  private $autoloader;

  protected function __construct() {

  }

  public static function init() {
    if (!(self::$app instanceof App)) {
      self::$app = new App();
    }
    return self::$app;
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
  public function addAutoloadPrefixes($callback, $prefixes = array()) {
    // Add prefixes for default MyTravel
    $prefixes = array_merge(
      array(array('MyTravel', 'modules')), $prefixes
    );
    foreach ($prefixes as $prefix) {
      call_user_func_array(array($this->autoloader, $callback), $prefix);
    }
    // Support method chaining
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

}
