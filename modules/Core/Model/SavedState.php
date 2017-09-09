<?php

namespace MyTravel\Core\Model;

/**
 * @todo Needs a system to prevent key collusion / conflict.
 * @todo State keys need to be constant and unique.
 * @todo Something like an App state key collecting/collector and saving to db with the key ID and string NAME.
 */
class SavedState {

  private $key;
  private $state;

  public function __construct($key = null) {
    $this->key = $key;
  }

  public function getState() {
    return (object) $this->state;
  }

  public function setState($state) {
    $this->state = (object) $state;
  }

}
