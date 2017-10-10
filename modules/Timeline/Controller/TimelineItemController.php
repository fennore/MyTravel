<?php

namespace MyTravel\Timeline\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use MyTravel\Core\Controller\ItemController;

/**
 * 
 *
 */
class TimelineItemController {
  public function importLegacyData() {
    // 1 . Get legacy data
    $finder = Finder::create()
      ->files()
      ->in('import')
      ->name('import.images.json');
    foreach($finder as $file) {
      $json = $file->getContents();
    }
    $dataList = json_decode($json);
    // 2 . Get timelineitems
    $ctrl = new ItemController('MyTravel\Timeline\Model\TimelineItem');
    $itemList = $ctrl->getItemList();

    foreach($itemList as $timelineItem) {
      $key = pathinfo($timelineItem->file->source, PATHINFO_BASENAME);
      if(isset($dataList->$key)) {
        // Trigger jsonserialize
        json_encode($timelineItem);
        // Update item
        $data = $dataList->$key;
        $timelineItem
          ->setting
          ->location = array(
          'lat' => (float) $data->lat,
          'lng' => (float) $data->lng
        );
        $timelineItem
          ->setTitle($data->title)
          ->setContent(strip_tags($data->description))
          ->update();
      } else {
        // Disable item
        $timelineItem->status = 0;
      }
    }
    return new Response('imported legacy data');
  }
}
