<?php

namespace MyTravel\Core\Service;

use MyTravel\Core\ServiceFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use MyTravel\Core\Controller\App;
use MyTravel\Core\Controller\YamlConfigLoader;
use MyTravel\Core\Config\ApplicationConfiguration;
use MyTravel\Core\Config\DatabaseConfiguration;
use MyTravel\Core\Config\ModuleConfiguration;
use MyTravel\Core\Config\DirectoryConfiguration;
use MyTravel\Core\Config\RoutingConfiguration;

final class Config implements ServiceFactoryInterface {

  protected static $config;
  private $configurationTree;
  private $fileConfig = array();

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
      self::$config->configurationTree = self::$config->getBasicConfig();
    }
    return self::$config;
  }

  public function __isset($name) {
    return isset($this->configurationTree[$name]);
  }

  public function __get($name) {
    return $this->configurationTree[$name] ?? null;
  }

  protected function getBasicConfig() {
    // Wrap in a try as a config file is optional and can be pure default values
    try {
      $this->fileConfig = $this->getConfigFromFile();
    } catch (Throwable $ex) {
      // Rethrow any throwable as it will be caught on the next level
      throw $ex;
    } finally {
      // Set config processor
      $processor = new Processor();
      $preserveKeys = array('directories', 'modules', 'database', 'routing');
      $appFileConfig = array_diff_key($this->fileConfig, array_flip($preserveKeys));
      // Only keep unused fileConfig
      $this->fileConfig = array_intersect_key($this->fileConfig, array_flip($preserveKeys));
      // Load config defaults for application
      $appConfig = $processor->processConfiguration(
        new ApplicationConfiguration(), array($appFileConfig)
      );

      return $appConfig;
    }
  }
  
  /**
   * Add configuration to the tree.
   * Each section can be added once, or will just be ignored.
   * @param ConfigurationInterface $configuration The configuration class to validate the configuration with
   * @param type $key The configuration section name
   */
  protected function addToBasicConfig(ConfigurationInterface $configuration, $key) {
    if(!isset($this->fileConfig[$key]) && isset($this->configurationTree[$key])) {
      // @todo Set some message
      return;
    }
    // Set config processor
    $processor = new Processor();
    // Load module configuration
    $this->configurationTree[$key] = $processor->processConfiguration(
      $configuration, array($this->fileConfig[$key] ?? array())
    );
    unset($this->fileConfig[$key]);  
  }
  
  public function addModuleConfig() {
    $this->addToBasicConfig(new ModuleConfiguration, 'modules');
  }
  
  public function addDirectoryConfig() {
    $this->addToBasicConfig(new DirectoryConfiguration, 'directories');
    // Create directories
    $this->createDirectories();
  }
  
  public function addDatabaseConfig() {
    $this->addToBasicConfig(new DatabaseConfiguration, 'database');
  }

  public function addRoutingConfig() {
    $this->addToBasicConfig(new RoutingConfiguration, 'routing');
  }

  /**
   * Load configuration from file
   * @return array
   */
  protected function getConfigFromFile() {
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
   * Create directories
   */
  private function createDirectories() {
    foreach($this->configurationTree['directories'] as $directory) {
      if(!is_dir($directory)) {
        mkdir($directory, 0750, true);
      }
    }
    return $this;
  }

}
