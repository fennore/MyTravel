<?php
namespace MyTravel\Core\Model;

use MyTravel\Core\TemplateInterface;

/**
 * The page object.
 * Contains all data needed to show a full page,
 * through a given template file.
 */
final class Page implements TemplateInterface {

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

  /**
   *
   * @param mixed $template string or array of template name (with extension and subdirectory)
   * @param array $variables array of named variables to use in a template
   */
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
