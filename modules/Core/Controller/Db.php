<?php

namespace MyTravel\Core\Controller;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManager;
use MyTravel\Core\ServiceFactoryInterface;

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
    $dbConfig = $this->getConfiguration();
    $connections = Config::get()->database['connections'];
    $this->connection[$name] = EntityManager::create(
        $connections[$name], $dbConfig
    );
    $this->sync();
    return $this;
  }

  /**
   * Get ORM Config.
   * @todo figure out how to return one for all modules
   * (with automatic updates?)
   * @return Doctrine\ORM\Configuration
   */
  protected function getConfiguration() {
    // get a schema from yaml file from all modules
    $moduleList = Modules::get()->all();
    $paths = array('./modules/Core/Mapping');
    foreach ($moduleList as $module) {
      $paths[] = './modules/' . $module->name . '';
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
    // Get current db schema from connection
    $fromSchema = $this->connection[$name]
      ->getConnection()
      ->getSchemaManager()
      ->createSchema();
    // Create schema from yml mapping files
    $tool = new SchemaTool($this->connection[$name]);
    $toSchema = $tool->getSchemaFromMetadata($metaDataClassList);
    $platform = $this->connection[$name]
      ->getConnection()
      ->getDatabasePlatform();

    $sql = $fromSchema->getMigrateToSql(
      $toSchema, $platform
    );
    foreach ($sql as $query) {
      $this->connection[$name]
        ->getConnection()
        ->query($query);
    }
    return $this;
  }

}
