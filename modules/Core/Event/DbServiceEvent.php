<?php

namespace MyTravel\Core\Event;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\EntityManager;

class DbServiceEvent extends Event {

  private $entityManager;

  public function __construct(EntityManager $entityManager) {
    $this->entityManager = $entityManager;
  }

  public function entityManager() {
    return $this->entityManager;
  }

}
