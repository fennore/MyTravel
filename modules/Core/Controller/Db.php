<?php

namespace MyTravel\Core\Controller;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use MyTravel\Core\ServiceFactoryInterface;
use MyTravel\Core\Model\Module;
use MyTravel\Core\Event\DbServiceEvent;
use MyTravel\Core\CoreEvents;

/**
 * @todo check for using more performant list iteration?
 * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/batch-processing.html#iterating-large-results-for-data-processing
 */
final class Db implements ServiceFactoryInterface {

  const BATCHSIZE = 50;

  protected static $dbServiceController;
  protected $connection;
  
  public function __isset($name) {
    return isset($this->connection[$name]);
  }

  public function __get($name) {
    return $this->connection[$name];
  }

  /**
   * Short alias for App::service()->get('db')->dbConnectionName
   * @param string $name database connection name you gave when connecting
   * @return Doctrine\ORM\EntityManager EntityManager
   */
  public static function get($name = 'sqlite') {
    return App::service()->get('db')->$name;
  }

  /**
   * Flush all connections
   */
  public static function flushAll() {
    foreach ((array) self::$dbServiceController->connection as $connection) {
      $connection->flush();
      $connection->clear();
    }
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

  /**
   * Connect to database.
   * This should only be called once.
   * @todo This will needs rework with possibility of connecting to multiple databases
   * @param type $name
   * @return $this
   */
  public function connect($name = 'sqlite') {
    // Get ORM config
    $dbConfig = $this->getConfiguration($name);
    $connections = Config::get()->database['connections'];
    $this->createSqliteDirectory();
    $this->connection[$name] = EntityManager::create(
        $connections[$name], $dbConfig
    );
    // Dispatch event for interacting with db entity manager before sync
    $event = new DbServiceEvent($this->connection[$name]);
    App::event()
      ->dispatch(CoreEvents::DBCONNECT, $event);
    $this->sync($name);
    return $this;
  }
  
  /**
   * Create sqlite directory if it does not exist
   */
  private function createSqliteDirectory() {
    $connections = Config::get()->database['connections'];
    foreach($connections as $connection) {
      if($connection['driver'] === 'pdo_sqlite' && !is_dir(pathinfo($connection['path'], PATHINFO_DIRNAME))) {
        mkdir(pathinfo($connection['path'], PATHINFO_DIRNAME), 0750, true);
      }
    }
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
    return Setup::createYAMLMetadataConfiguration($paths, (App::get()->inDevelopment() || !Config::get()->database['use_cache']));
  }

  /**
   * Tweak schema diff removing some items that appear,
   * even when nothing changed.
   * This may in some cases clean too much.
   * @return type
   */
  protected function diffSafeSql(AbstractPlatform $platform, Schema $fromSchema, Schema $toSchema) {
    $schemaDiff = \Doctrine\DBAL\Schema\Comparator::compareSchemas($fromSchema, $toSchema);
    // Crudely remove some diff items that always appear even when nothing changed
    foreach ($schemaDiff->changedTables as $table) {
      if (count($table->addedForeignKeys) === 2) {
        $table->addedForeignKeys = array();
      }
      if (!empty($table->changedColumns['id']->changedProperties)) {
        $k = array_search('autoincrement', $table->changedColumns['id']->changedProperties);
        unset($table->changedColumns['id']->changedProperties[$k]);
      }
      if (empty($table->changedColumns['id']->changedProperties) && isset($table->changedColumns['id'])) {
        unset($table->changedColumns['id']);
      }
    }
    return $schemaDiff->toSaveSql($platform);
  }

  /**
   * Synchronize database with newest database structure from code.
   * Run forceSync when some changes do not get through this crude cleaned version.
   * @todo Only run this when there are changes.
   *  => something wrong with default comparator always dropping and recreating all
   * @todo Look for updated DBAL version or fix for compare with class table inheritance?
   * @param type $name
   * @return $this
   */
  protected function sync($name = 'sqlite') {
    if (!App::get()->inDevelopment()) {
      return;
    }
    // Connection
    $connection = $this->connection[$name]->getConnection();
    // Gather all database mapping
    $metaDataClassList = $this->connection[$name]
      ->getMetadataFactory()
      ->getAllMetadata();
    $fromSchema = $connection
      ->getSchemaManager()
      ->createSchema();
    // Create schema from yml mapping files
    $tool = new SchemaTool($this->connection[$name]);
    $toSchema = $tool->getSchemaFromMetadata($metaDataClassList);
    //
    $updateSchemaSql = $this->diffSafeSql(
      $connection->getDatabasePlatform(), $fromSchema, $toSchema
    );
    foreach ($updateSchemaSql as $sql) {
      $connection->executeQuery($sql);
    }
    return $this;
  }

  /**
   * Do a dumb brute force sync.
   * Only run this when something goes wrong with sync().
   * @param type $name
   */
  protected function forceSync($name = 'sqlite') {
    if (!App::get()->inDevelopment()) {
      return;
    }
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
