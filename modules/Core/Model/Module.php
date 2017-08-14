<?php

namespace MyTravel\Core\Model;

use ErrorException;
use ReflectionClass;
use MyTravel\Core\Controller\App;

/**
 * Module wrapper class,
 * through which the application interacts with all modules
 */
class Module {

  protected $name;
  protected $controller;

  public function __construct($moduleName) {
    $this->name = $moduleName;
  }
  /**
   * Load the module
   * This will register the module to the autoloader,
   * and register event listeners for setup
   * @todo
   * - this is the place where we can add active / inactive module
   * - we can / must unregister an inactive module with the register
   */
  public function load() {
    // Register the module to the autoloader
    App::get()
      ->addAutoloadPrefix('MyTravel\\' . $this->name, 'modules\\' . $this->name);
    // Check if Class actually exists and not just the file
    $moduleControllerClass = 'MyTravel\\' . $this->name .
      '\Controller\\' . $this->name . 'Controller';
    if (!class_exists($moduleControllerClass)) {
      $msg = 'Found controller file for module ' . $this->name . ' but class / namespace is missing.';
      throw new ErrorException($msg);
    }
    // Check if the Module Controller has proper inheritance
    $checkClass = new ReflectionClass($moduleControllerClass);
    $interfaceNames = $checkClass->getInterfaceNames();
    
    if (!in_array('MyTravel\Core\ModuleInterface', $interfaceNames)) {
      $msg = 'Module ' . $this->name . ' should inherit from ModuleInterface.';
      throw new ErrorException($msg);
    }
    call_user_func_array(array($moduleControllerClass, 'load'), array());
  }

}
