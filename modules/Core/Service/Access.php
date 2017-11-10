<?php

namespace MyTravel\Core\Service;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use MyTravel\Core\ServiceFactoryInterface;
use MyTravel\Core\Controller\App;

/**
 */
class Access implements ServiceFactoryInterface, AuthorizationCheckerInterface {
  private static $controller;
  
  protected function __construct() {

  }
  /**
   * Alias for App::service()->get(access)
   * @return Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
   */
  public static function get() {
    return App::service()->get('access');
  }
  /**
   * 
   * @param mixed $accessKey Can be string or array
   * @return boolean
   */
  public static function granted($accessKey) {
    return self::get()->isGranted($accessKey);
  }
  public static function setService() {
    if(!self::$controller instanceof self) {
      self::$controller = new self();
    }
    return self::$controller;
  }
  /**
   * Simple Access check:
   *  - grant access to all GET and HEAD requests
   *  - grant access if environment is set to development
   * @return boolean
   */
  public function isGranted($accessKey, $subject = NULL) {
    return in_array(App::get()->getRequest()->getMethod(), array('GET', 'HEAD')) || App::get()->inDevelopment();
  }

}
