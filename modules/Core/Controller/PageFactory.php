<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpFoundation\Request;

/**
 * @todo should use database and stuff
 */
class PageFactory {

  public static function viewHomePage(Request $request) {
    $templateVariables = array('page' => 'home');
    return $templateVariables;
  }
  public static function viewAboutPage(Request $request) {
    $templateVariables = array('page' => 'about');
    return $templateVariables;
  }
  public static function viewEditImagePage(Request $request) {
    $templateVariables = array('page' => 'edit image');
    return $templateVariables;
  }

}
