<?php
namespace MyTravel\User\Controller;

use MyTravel\Core\ModuleControllerInterface;
use MyTravel\Core\Controller\App;
use Symfony\Component\Security\Http\Firewall;
use Symfony\Component\Security\Http\FirewallMap;

class UserController implements ModuleControllerInterface {
  
  protected static $controller;
  
  protected function __construct() {
  }
  
  public static function load() {
    if (!(self::$controller instanceof UserController)) {
      self::$controller = new UserController();
    }
    return self::$controller;
  }
  
  public function init() {
    //App::get()->addService('access', '::setService');
   
    
    // testing
    $listeners = array(
      new Firewall\UsernamePasswordJsonAuthenticationListener(),
      new Firewall\RememberMeListener(),
      new Firewall\LogoutListener(),
      new Firewall\AnonymousAuthenticationListener()
    );
    $fireWallMap = new FirewallMap();
    $fireWallMap->add($requestMatcher, $listeners);
    $fireWall = new Firewall($map, App::event());
    App::event()->addSubscriber($fireWall);
    
  }
}
