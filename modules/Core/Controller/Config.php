<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Definition\Processor;

class Config {

  protected static $config;
  private $configurationTree;

  protected function __construct() {

  }

  public static function get() {
    if (!(self::$config instanceof Config)) {
      self::$config = new Config();
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
      $configDirectories = array('./config');
      $locator = new FileLocator($configDirectories);
      // for now we only support the one and only config.yml


      $configFile = $locator->locate('config.yml', null, true);
      $configResolvers = array(
        new YamlConfigLoader($locator)
      );

      $loaderResolver = new LoaderResolver($configResolvers);
      $delegatingLoader = new DelegatingLoader($loaderResolver);

      $fileConfig = $delegatingLoader->load($configFile);
    } catch (Throwable $ex) {
      // throw any throwable as it will be caught on the next level
      throw $ex;
    } finally {
      // Load config processor
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
      /* $routingConfig = $processor->processConfiguration(
        new RoutingConfiguration(), array($routingFileConfig)
        ); */
      $fullConfig = $appConfig +
        array('database' => $dbConfig) /* +
        array('routing' => $routingConfig) */
      ;
      return $fullConfig;
    }
  }

}
