<?php

namespace MyTravel\Core\Model;

use OverflowException;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Controller\Config;

/**
 * Module wrapper class,
 * through which the application interacts with all modules
 */
class Module {

  protected $name;
  protected $controller;
  protected $status;
  protected $active;

  public function __construct($moduleName) {
    $this->name = $moduleName;
  }
  /**
   * Only load the controller once.
   * @param string $moduleControllerClass
   * @throws OverflowException When controller is already loaded
   */
  private function checkIfAlreadyLoaded($moduleControllerClass) {
    if (isset($this->controller) && $this->controller instanceof $moduleControllerClass) {
      $msg = 'Controller for module ' . $this->name . ' already set.';
      throw new OverflowException($msg);
    }
  }

  /**
   * Load the module.
   * This will register the module to the autoloader,
   * and load module configuration.
   * @todo
   * - we can / must unregister an inactive module with the register
   */
  public function load() {
    $moduleControllerClass = 'MyTravel\\' . $this->name .
      '\Controller\\' . $this->name . 'Controller';
    $this->checkIfAlreadyLoaded($moduleControllerClass);
    $this->controller = call_user_func_array(array($moduleControllerClass, 'load'), array());
    // Load module configuration
    $this->status = Config::get()->modules[$this->name]['status'] ?? 'prod';
    $this->active = Config::get()->modules[$this->name]['active'] ?? true;
    //
    // Return for method chaining
    return $this;
  }

}
