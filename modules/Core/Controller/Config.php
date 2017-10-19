<?php

namespace MyTravel\Core\Controller;

use MyTravel\Core\ServiceFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Definition\Processor;
use MyTravel\Core\Config\ApplicationConfiguration;
use MyTravel\Core\Config\DatabaseConfiguration;
use MyTravel\Core\Config\ModuleConfiguration;
use MyTravel\Core\Config\DirectoryConfiguration;

final class Config implements ServiceFactoryInterface {

  protected static $config;
  private $configurationTree;

  protected function __construct() {

  }

  /**
   * Short alias for App::service()->get('config')
   * @return Config
   */
  public static function get() {
    return App::service()->get('config');
  }

  /**
   * Set config as service
   * @return self
   */
  public static function setService() {
    if (!(self::$config instanceof self)) {
      self::$config = new self();
      self::$config->configurationTree = self::$config->buildBasicConfig();
    }
    return self::$config;
  }

  public function __isset($name) {
    return isset($this->configurationTree[$name]);
  }

  public function __get($name) {
    return $this->configurationTree[$name] ?? null;
  }

  protected function buildBasicConfig() {
    // Wrap in a try as a config file is optional and can be pure default values
    $fileConfig = array();
    try {
      $fileConfig = $this->loadFromFile();
    } catch (Throwable $ex) {
      // Rethrow any throwable as it will be caught on the next level
      throw $ex;
    } finally {
      // Set config processor
      $processor = new Processor();
      $appFileConfig = array_diff_key($fileConfig, array('directories' => null, 'modules' => null, 'database' => null, 'routing' => null));
      $dbFileConfig = $fileConfig['database'] ?? array();
      $routingFileConfig = $fileConfig['routing'] ?? array();
      $moduleFileConfig = $fileConfig['modules'] ?? array();
      $directoryFileConfig = $fileConfig['directories'] ?? array();

      // Load config defaults for application
      $appConfig = $processor->processConfiguration(
        new ApplicationConfiguration(), array($appFileConfig)
      );
      
      $basicConfig = $appConfig +
        array('database' => $dbFileConfig) +
        array('routing' => $routingFileConfig) +
        array('modules' => $moduleFileConfig) +
        array('directories' => $directoryFileConfig)
      ;
      return $basicConfig;
    }
  }
  
  public function addModuleConfig() {
    // Set config processor
    $processor = new Processor();
    // Load module configuration
    $moduleConfig = $processor->processConfiguration(
      new ModuleConfiguration(), array($this->configurationTree['modules'])
    );
    $this->configurationTree['modules'] = $moduleConfig;
  }
  
  public function addDirectoryConfig() {
    // Set config processor
    $processor = new Processor();
    // Load directories
    $directoryConfig = $processor->processConfiguration(
      new DirectoryConfiguration(), array($this->configurationTree['directories'])
    );
    $this->configurationTree['directories'] = $directoryConfig;
    // Create directories
    self::$config->createDirectories();
  }
  
  public function addDatabaseConfig() {
    // Set config processor
    $processor = new Processor();
    // Load database schema, this can not be altered
    $dbConfig = $processor->processConfiguration(
      new DatabaseConfiguration(), array($this->configurationTree['database'])
    );
    $this->configurationTree['database'] = $dbConfig;
  }

  public function addRoutingConfig() {
    // Set config processor
    $processor = new Processor();
    // Load routing setup, only paths can be altered in config
    $routingConfig = $processor->processConfiguration(
      new RoutingConfiguration(), array($this->configurationTree['routing'])
    );
    $this->configurationTree['routing'] = $routingConfig;
  }

  /**
   * Load configuration from file
   * @return array
   */
  protected function loadFromFile() {
    // On a quest to load config from file
    $configDirectories = array('./config');
    $locator = new FileLocator($configDirectories);

    // For now we only support the one and only config.yml
    $configFile = $locator->locate('config.yml', null, true);
    $configResolvers = array(
      new YamlConfigLoader($locator)
    );

    $loaderResolver = new LoaderResolver($configResolvers);
    $delegatingLoader = new DelegatingLoader($loaderResolver);

    return $delegatingLoader->load($configFile);
  }

  /**
   * Make sure all configuration values contain proper values
   * @todo Probably want to make use of validation / filtering with the treebuilder
   * @deprecated
   */
  private function verify($config) {
    return $config;
  }
  
  /**
   * Create directories
   */
  private function createDirectories() {
    foreach($this->configurationTree['directories'] as $directory) {
      if(!is_dir($directory)) {
        mkdir($directory, 0750, true);
      }
    }
  }

}
