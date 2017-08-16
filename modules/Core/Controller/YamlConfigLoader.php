<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlConfigLoader extends FileLoader {

  public function load($resource, $type = null) {
    $configContents = file_get_contents($resource);
    return Yaml::parse($configContents);
  }

  public function supports($resource, $type = null) {
    return is_string($resource) &&
      'yml' === pathinfo($resource, PATHINFO_EXTENSION);
  }

}
