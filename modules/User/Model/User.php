<?php
namespace MyTravel\User\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

class User implements UserInterface, EquatableInterface {
  
  protected $id;
  protected $username;
  protected $password;
  protected $salt;
  protected $roles;
  
  public function __construct($username, $password, $salt, array $roles) {
    $this->username = $username;
    $this->password = $password;
    $this->salt = $salt;
    $this->roles = $roles;
  }
  
  public function getId() {
    return $this->id;
  }
  public function getUsername() {
    return $this->username;
  }
  public function getPassword() {
    return $this->password;
  }
  public function getSalt() {
    return $this->salt;
  }
  public function getRoles() {
    return $this->roles;
  }
  public function eraseCredentials() {
    ;
  }
  public function isEqualTo(UserInterface $user) {
    return $this->id === $user->getId() && $user instanceof User;
  }
  
}
