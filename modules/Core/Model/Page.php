<?php
namespace MyTravel\Core\Model;

/**
 * The page object.
 * Contains all data needed to show a full page.
 * For now just dumbly shows a static page template.
 * @todo pretty much everything
 *  - link to database
 *  - figure out how far it should go
 *  - should probably contain everyting (menu, blocks, .....)
 *    or at least the information for everything
 */
class Page {
  private $template;

  /**
   *
   * @param string $template The path to the template file
   */
  public function __construct($template) {
    $this->template = $template;
  }

  /**
   * Page output
   * @todo pretty much everything
   * @return noideayet
   */
  public function view() {
    // should return the file content, not the path
    return $this->template;
  }

}
