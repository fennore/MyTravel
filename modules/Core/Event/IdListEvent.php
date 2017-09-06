<?php

namespace MyTravel\Core\Event;

use Symfony\Component\EventDispatcher\Event;

class IdListEvent extends Event {

  private $idList;

  /**
   *
   * @param array $idList
   */
  public function __construct(Array $idList) {
    $this->idList = $idList;
  }

  public function getIdList() {
    return $this->idList;
  }

}
