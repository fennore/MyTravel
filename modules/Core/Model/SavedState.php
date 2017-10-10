<?php

namespace MyTravel\Core\Model;

use stdClass;

/**
 * SavedState Doctrine Entity
 * @todo Needs a system to prevent key collusion / conflict.
 * @todo State keys need to be constant and unique.
 * @todo Something like an App state key collecting/collector and saving to db with the key ID and string NAME.
 */
class SavedState {

  /**
   * Unique SavedState identifier
   * @var int
   */
  protected $key;

  /**
   * State object
   * @var stdClass
   */
  protected $state;

  /**
   * Note: never gets called by Doctrine ORM.
   */
  public function __construct($key = null) {
    $this->key = $key;
    $this->state = (object) array();
  }

  /**
   * Called on postLoad Entity life cycle
   * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#lifecycle-callbacks
   */
  public function postLoad() {
    $this->state = (object) $this->state;
  }

  /**
   * Get the full state object
   * @return stdClass
   */
  public function getState() {
    return (object) $this->state;
  }

  /**
   * Overwrite the full state object
   * Warning!: do not use numeric values for state names!
   * @param stdClass $state
   */
  public function setState(stdClass $state) {
    $this->state = (object) $state;
  }

  /**
   * Get a state value
   * @param string $name state name
   * @return null|mixed
   */
  public function get(string $name) {
    return $this->state->$name ?? null;
  }

  /**
   * Set a state value
   * Warning!: do not use numeric values for state names!
   * @param string $name state name
   * @param mixed $value
   */
  public function set(string $name, $value) {
    $this->state->$name = $value;
  }

  /**
   * Add a state value
   * Warning!: do not use numeric values for state names!
   * @param string $name state name
   * @param type $value
   */
  public function add(string $name, $value) {
    if (!isset($this->state->$name)) {
      $this->state->$name = array();
    }
    array_push($this->state->$name, $value);
  }

}
