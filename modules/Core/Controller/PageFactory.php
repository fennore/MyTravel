<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Model\Page;

/**
 * @todo should use database and stuff for PageItem.
 * A page should be an item of type PageItem.
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

  public static function viewItemPage(Request $request) {
    $ctrl = new ItemController();
    $itemList = $ctrl->getItemList(0, 25);
    if (!empty($request->attributes->get('title'))) {
      $item = $ctrl->getItemByTitle($request);
    } else if (!empty($itemList)) {
      $item = $itemList[0];
    }

    // Set Template suggestions
    $template = array(
      'item.tpl'
    );
    if (!empty($request->attributes->get('_type'))) {
      // Dummy item
      $classCall = $request->attributes->get('_type');
      $dummy = new $classCall();
      array_unshift($template, 'item-' . $dummy->getType() . '.tpl');
    }
    // Set data
    $variables = array(
      'item' => $item ?? null,
      'itemList' => $itemList
    );
    return new Page($template, $variables);
  }

}
