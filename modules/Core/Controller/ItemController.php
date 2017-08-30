<?php

namespace MyTravel\Core\Controller;

use DateTime;
use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Model\Item;

class ItemController {

  public function view(Request $request) {
    
  }

  public function output(Request $request) {
    
  }

  public function create(Request $request) {
    $date = new DateTime();
    $item = new Item(array(
      'status' => 1,
      'weight' => 0,
      'timestamp' => $date->getTimestamp(),
      // User provided
      'title' => $request->request->get('title'),
      'content' => $request->request->get('content'),
    ));
    Db::get()->persist($item);
    Db::get()->flush();
    return $item;
  }

  public function update(Request $request) {
    
  }

  public function delete(Request $request) {
    
  }

}
