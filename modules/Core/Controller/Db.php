<?php

namespace MyTravel\Core\Controller;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManager;
use MyTravel\Core\ServiceFactoryInterface;
use MyTravel\Core\Model\Module;
use MyTravel\Core\Event\DbServiceEvent;

final class Db implements ServiceFactoryInterface {

  protected static $dbServiceController;
  protected $connection;

  /**
   * Short alias for App::service()->get('config')->dbConnectionName
   * @param string $name database connection name you gave when connecting
   * @return MyTravel\Core\Controller\Db
   */
  public static function get($name = 'sqlite') {
    return App::service()->get('db')->$name;
  }

  /**
   * Set config as service
   * @return self
   */
  public static function setService() {
    if (!(self::$dbServiceController instanceof self)) {
      self::$dbServiceController = new self();
    }
    return self::$dbServiceController;
  }

  public function connect($name = 'sqlite') {
    $dbConfig = $this->getConfiguration($name);
    $connections = Config::get()->database['connections'];
    $this->connection[$name] = EntityManager::create(
        $connections[$name], $dbConfig
    );
    // Dispatch event for altering application config node
    $event = new DbServiceEvent($this->connection[$name]);
    App::event()
      ->dispatch('module.service.db.connect', $event);
    $this->sync();
    return $this;
  }

  /**
   * Get ORM Config.
   * @return Doctrine\ORM\Configuration
   */
  protected function getConfiguration($name = 'sqlite') {
    // Get a schema from yaml file from all modules
    $moduleList = Modules::get()->all();
    // Add Core module to the list
    array_push($moduleList, new Module('Core'));
    foreach ($moduleList as $module) {
      // Mapping for all in root
      $mappingPath = './modules/' . $module->name . '/Mapping';
      if (!\is_dir($mappingPath)) {
        continue;
      }
      $paths[] = $mappingPath;
    }
    //
    // see what it returns
    // and look for best cross-module implementation
    return Setup::createYAMLMetadataConfiguration($paths, false);
  }
  /**
   * Synchronize database with newest database structure from code.
   * @todo Only run this when there are changes.
   * @param type $name
   * @return $this
   */
  protected function sync($name = 'sqlite') {
    // Gather all database mapping
    $metaDataClassList = $this->connection[$name]
      ->getMetadataFactory()
      ->getAllMetadata();
    // Create schema from yml mapping files
    $tool = new SchemaTool($this->connection[$name]);
    $tool->updateSchema($metaDataClassList, true);
    return $this;
  }

}
