<?php

namespace MyTravel\Core\Model;

use ErrorException;
use OverflowException;
use ReflectionClass;
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
   * Validates the controller class when the file has been found.
   * The controller must inherit from ModuleControllerInterface.
   * The classname and namespace must be set according to PSR-4.
   * @param string $moduleControllerClass
   * @throws ErrorException
   */
  private function validateControllerClass($moduleControllerClass) {
    // Check if Class actually exists and not just the file
    if (!class_exists($moduleControllerClass)) {
      $msg = 'Found controller file for module ' . $this->name . ' but class / namespace is missing.';
      throw new ErrorException($msg);
    }
    // Check if the Module Controller has proper inheritance
    $checkClass = new ReflectionClass($moduleControllerClass);
    $interfaceNames = $checkClass->getInterfaceNames();

    if (!in_array('MyTravel\Core\ModuleControllerInterface', $interfaceNames)) {
      $msg = 'Module ' . $this->name . ' should inherit from ModuleControllerInterface.';
      throw new ErrorException($msg);
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
    // Register the module to the autoloader
    App::get()
      ->addAutoloadPrefix('MyTravel\\' . $this->name, 'modules\\' . $this->name);
    $this->validateControllerClass($moduleControllerClass);
    $this->controller = call_user_func_array(array($moduleControllerClass, 'load'), array());
    // Load module configuration
    $this->status = Config::get()->modules[$this->name]['status'] ?? 'prod';
    $this->active = Config::get()->modules[$this->name]['active'] ?? true;
    //
    // Return for method chaining
    return $this;
  }

}
