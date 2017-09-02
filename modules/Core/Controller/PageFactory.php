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
  public static function viewEditImagePage(Request $request) {
    $files = new FileController();
    $files->sync();
    $templateVariables = array('page' => 'edit image');
    return new Page('edit-image.tpl', $templateVariables);
  }
  public static function viewItemPage(Request $request) {
    $type = $request
      ->attributes
      ->get('_type');
    $pathTitle = $request->attributes->get('title');
    $ctrl = new ItemController($type);
    $itemList = $ctrl->getItemList();
    if (!empty($pathTitle)) {
      $item = $ctrl->getItemByTitle($pathTitle);
    } else {
      $item = $itemList[0];
    }

    // Set Template suggestions
    $template = array(
      'item-' . $item->getType() . '.tpl',
      'item.tpl'
    );
    // Set data
    $variables = array(
      'item' => $item,
      'itemList' => $itemList
    );
    return new Page($template, $variables);
  }

}
