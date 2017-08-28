<?php

namespace MyTravel\Core\Controller;

use MyTravel\Core\ServiceFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Definition\Processor;

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
      self::$config->configurationTree = self::$config->buildConfig();
    }
    return self::$config;
  }

  public function __isset($name) {
    return isset($this->configurationTree[$name]);
  }

  public function __get($name) {
    return $this->configurationTree[$name];
  }

  protected function buildConfig() {
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
      $appFileConfig = array_diff_key($fileConfig, array('database' => null, 'routing' => null));
      $dbFileConfig = $fileConfig['database'] ?? array();
      $routingFileConfig = $fileConfig['routing'] ?? array();

      // Load config defaults for application
      $appConfig = $processor->processConfiguration(
        new ApplicationConfiguration(), array($appFileConfig)
      );
      // Load database schema, this can not be altered
      $dbConfig = $processor->processConfiguration(
        new DatabaseConfiguration(), array($dbFileConfig)
      );
      // Load routing setup, only paths can be altered in config
      $routingConfig = $processor->processConfiguration(
        new RoutingConfiguration(), array($routingFileConfig)
      );
      $fullConfig = $appConfig +
        array('database' => $dbConfig) +
        array('routing' => $routingConfig)
      ;
      return $this->verify($fullConfig);
    }
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
   */
  private function verify($config) {
    $config['basepath'] = \preg_replace('/\/+/', '/', '/' . $config['basepath'] . '/');
    return $config;
  }

}
