<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Model\Page;

/**
 * @todo should use database and stuff
 */
class PageFactory {

  public static function viewHomePage(Request $request) {
    $templateVariables = array('page' => 'home');
    return new Page('home.tpl', $templateVariables);
  }
  public static function viewAboutPage(Request $request) {
    $templateVariables = array('page' => 'about');
    return new Page('about.tpl', $templateVariables);
  }
  public static function viewEditImagePage(Request $request) {
    $templateVariables = array('page' => 'edit image');
    return new Page('edit-image.tpl', $templateVariables);
  }

}
