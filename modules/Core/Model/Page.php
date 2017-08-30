<?php
namespace MyTravel\Core\Model;

use MyTravel\Core\TemplateInterface;

/**
 * The page object.
 * Contains all data needed to show a full page,
 * through a given template file.
 */
class Page implements TemplateInterface {

  /**
   * Views subpath to template file
   * @var string
   */
  private $template;

  /**
   * Variables for the template file
   * @var array
   */
  private $variables;

  public function __construct($template, $variables) {
    $this->template = $template;
    $this->variables = $variables;
  }

  public function getTemplate() {
    return $this->template;
  }

  public function getVariables() {
    return $this->variables;
  }

}
