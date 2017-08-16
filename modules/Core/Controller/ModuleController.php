<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Finder\Finder;
use MyTravel\Core\Model\Module;

class ModuleController {
  protected static $self;
  private $modules;

  protected function __construct() {

  }

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

  private function findModules() {
    $moduleList = array();
    $finder = new Finder();
    $finder->files()->in('./modules')->depth('<3');
    foreach ($finder as $item) {
      $pathSections = preg_split('/\/|\\\\/', $item->getRelativePathname());
      $moduleName = array_shift($pathSections);
      $fileName = array_pop($pathSections);
      $validFile = $fileName === $moduleName . 'Controller.php';
      $validDirectory = isset($pathSections[0]) ? $pathSections[0] === 'Controller' : false;
      if ($validFile && $validDirectory) {
        array_push($moduleList, new Module($moduleName));
      }
    }
    return $moduleList;
  }

}
