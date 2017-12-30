<?php

namespace MyTravel\User\Model;

use \Serializable;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements AdvancedUserInterface, Serializable, EquatableInterface {

  protected $id;
  protected $username;
  protected $password;
  protected $salt;
  protected $roles;
  protected $status;
  protected $created;

  public function __construct($username, $password, $salt, array $roles) {

    if ('' === $username || null === $username) {
      throw new \InvalidArgumentException('The username cannot be empty.');
    }

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

  public function isAccountNonExpired() {
    return true;
  }

  public function isAccountNonLocked() {
    return true;
  }

  public function isCredentialsNonExpired() {
    return true;
  }

  public function isEnabled() {
    return $this->status > 0;
  }

  public function serialize() {
    return serialize(array(
      $this->id,
      $this->username,
      $this->password,
      $this->status
    // see section on salt below
    // $this->salt,
    ));
  }

  public function unserialize($serialized) {
    list(
      $this->id,
      $this->username,
      $this->password,
      $this->status
      // see section on salt below
      // $this->salt
    ) = unserialize($serialized);
  }

}
