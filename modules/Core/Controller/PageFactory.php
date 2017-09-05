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
    $pathTitle = $request->getPathInfo();
    $ctrl = new ItemController();
    $itemList = $ctrl->getItemList();
    if (!empty($request->attributes->get('title'))) {
      $item = $ctrl->getItemByTitle($pathTitle);
    } else if (!empty($itemList)) {
      $item = $itemList[0];
    }

    // Set Template suggestions
    $template = array(
      'item.tpl'
    );
    if (!empty($item)) {
      array_unshift($template, 'item-' . $item->getType() . '.tpl');
    }
    // Set data
    $variables = array(
      'item' => $item ?? null,
      'itemList' => $itemList
    );
    return new Page($template, $variables);
  }

}
