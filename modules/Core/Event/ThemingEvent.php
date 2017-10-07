<?php

namespace MyTravel\Core\Event;

use Symfony\Component\EventDispatcher\Event;
use Twig\Environment;

class ThemingEvent extends Event {

  private $themer;

  public function __construct(Environment $themer) {
    $this->themer = $themer;
  }

  public function themer() {
    return $this->themer;
  }

}
