<?php

namespace MyTravel\Core\Controller;

use ErrorException;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use MyTravel\Core\Model\Module;

class ModuleController {
  protected static $self;
  private $modules;

  protected function __construct() {

  }

  /**
   * Return an array of Module instances
   * @return array
   */
  public static function getModules() {
    if (!(self::$self instanceof ModuleController)) {
      self::$self = new ModuleController();
      self::$self->modules = self::$self->findModules();
      foreach (self::$self->modules as $module) {
        $module->load();
      }
    }
    return self::$self->modules;
  }

  /**
   * Crawls the modules directory looking for valid modules.
   * A valid module has a map with the module name under the modules directory.
   * And a module controller class ModulenameController which
   * implements the Mytravel\Core\ModuleControllerInterface.
   *
   * @return array
   */
  private function findModules() {
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
      App::get()
        ->addAutoloadPrefix('MyTravel\\' . $moduleName, 'modules\\' . $moduleName);
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
  }

}
