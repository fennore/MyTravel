<?php
namespace MyTravel\User\Controller;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Mytravel\Db;

class DatabaseUserProvider implements UserProviderInterface {
  public function loadUserByUsername($username) {
    Db::get()
      ->createQueryBuilder();
  }
  public function refreshUser(UserInterface $user) {
    ;
  }
  public function supportsClass($class) {
    ;
  }
}
