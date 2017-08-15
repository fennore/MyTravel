<?php

namespace MyTravel\Core;

interface ModuleInterface {
  public static function load();

  public function init();
}
