<?php
namespace MyTravel\User\Service;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use MyTravel\Core\ServiceFactoryInterface;
use Mytravel\Db;
use MyTravel\User\Model\User;

class Users implements UserProviderInterface, ServiceFactoryInterface {
  public static function get() {
    return App::service()->get('users');
  }
  public static function setService() {
    return $this;
  }
  public function loadUserByUsername($username) {
    $qb = Db::get()
      ->createQueryBuilder();
    $expr = $qb
      ->expr()
      ->eq('u.username', ':username');
    $qb
      ->select('u')
      ->from('MyTravel\User\Model\User', 'u')
      ->where($expr)
      ->setParameter(':username', $username);
    return $qb->getQuery()->getResult();
  }
  public function refreshUser(UserInterface $user) {
    return $this->loadUserByUsername($user->getUsername());
  }
  public function supportsClass($class) {
    return User::class === $class;
  }
}
