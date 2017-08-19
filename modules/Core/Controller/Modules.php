<?php

namespace MyTravel\Core\Controller;

use ErrorException;
use ReflectionClass;
use MyTravel\Core\ServiceFactoryInterface;
use MyTravel\Core\Model\Module;
use Symfony\Component\Finder\Finder;


final class Modules implements ServiceFactoryInterface {

  protected static $controller;
  private $modules;

  protected function __construct() {

  }

  public static function get() {
    return App::service()->get('modules');
  }

  public static function setService() {
    if (!(self::$controller instanceof self)) {
      self::$controller = new self();
    }
    return self::$controller;
  }

  /**
   * Load the modules.
   * This loads all things necessary before actually
   * firing them up.
   * @return $this
   */
  public function load() {
    if (!isset($this->modules)) {
      $this->modules = $this->find();
    }
    foreach ($this->modules as $module) {
      $module->load();
    }
    return $this;
  }
  /**
   * Initialize loaded modules
   * @throws ErrorException
   */
  public function init() {
    if (!isset($this->modules)) {
      $msg = 'Before initializing you need to load the modules';
      throw new ErrorException($msg);
    }
    foreach ($this->modules as $module) {
      $module->init();
    }
    return $this;
  }

  /**
   * Crawls the modules directory looking for valid modules.
   * A valid module has a map with the module name under the modules directory.
   * And a module controller class ModulenameController which
   * implements the Mytravel\Core\ModuleControllerInterface.
   *
   * @return array
   */
  private function find() {
    $moduleList = array();
    $finder = new Finder();
    $finder->files()->in('./modules')->depth('<3');
    foreach ($finder as $item) {
      $pathSections = preg_split('/\/|\\\\/', $item->getRelativePathname());
      $moduleName = array_shift($pathSections);
      $fileName = array_pop($pathSections);
      $moduleControllerClass = 'MyTravel\\' . $moduleName .
        '\Controller\\' . $moduleName . 'Controller';
      $validFile = $fileName === $moduleName . 'Controller.php';
      $validDirectory = isset($pathSections[0]) ? $pathSections[0] === 'Controller' : false;
      // Add the module prefix to the autoloader
      if ($validFile && $validDirectory) {
        App::get()
          ->addAutoloadPrefix('psr-4', 'MyTravel\\' . $moduleName, 'modules\\' . $moduleName);
      }
      if ($validFile && $validDirectory && $this->validateControllerClass($moduleControllerClass)) {
        array_push($moduleList, new Module($moduleName));
      }
    }
    return $moduleList;
  }

  /**
   * Validates the controller class when the file has been found.
   * The controller must inherit from ModuleControllerInterface.
   * The classname and namespace must be set according to PSR-4.
   * @param string $moduleControllerClass
   * @return boolean
   * @throws ErrorException
   */
  private function validateControllerClass($moduleControllerClass) {
    // Check if Class actually exists and not just the file
    if (!class_exists($moduleControllerClass)) {
      $msg = 'Found ' . $moduleControllerClass . ' controller file but class / namespace is missing.';
      throw new ErrorException($msg);
    }
    // Check if the Module Controller has proper inheritance
    $checkClass = new ReflectionClass($moduleControllerClass);
    $interfaceNames = $checkClass->getInterfaceNames();

    if (!in_array('MyTravel\Core\ModuleControllerInterface', $interfaceNames)) {
      $msg = $moduleControllerClass . ' should inherit from ModuleControllerInterface.';
      throw new ErrorException($msg);
    }
    // Code managed to reach which means its a valid controller class
    return true;
  }

}
