<?php

namespace MyTravel\Core;

interface ServiceFactoryInterface {
  public static function get();

  public static function setService();
}
