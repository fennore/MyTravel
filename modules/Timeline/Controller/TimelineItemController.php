<?php

namespace MyTravel\Timeline\Controller;

use MyTravel\Core\Controller\Db;
use MyTravel\Core\Controller\FileController;
use MyTravel\Core\Controller\ItemController;
use MyTravel\Timeline\Model\TimelineItem;
use MyTravel\Core\Event\IdListEvent;

class TimelineItemController {

  /**
   * Callback for removed files listener
   * @todo check if this can be executed on Item itself and moved to Core,
   * instead of using hook.
   * @param IdListEvent $event
   */
  public function cleanGhostFiles(IdListEvent $event) {
    $qb = Db::get()->createQueryBuilder();
    $expr = $qb
      ->expr()
      ->in('t.file', ':ids');
    $qb
      ->update('MyTravel\Timeline\Model\TimelineItem', 't')
      ->set('t.file', ':setnull')
      ->where($expr)
      ->setParameter(':setnull', null)
      ->setParameter(':ids', $event->getIdList());
    $query = $qb->getQuery();
    $query->execute();
  }

  /**
   * Get all Files from database that are images.
   * This means filter on type like image/%.
   * @return array
   */
  private function getImages() {
    $qb = Db::get()->createQueryBuilder();
    $expr = $qb
      ->expr()
      ->like('f.type', ':type');
    $qb
      ->select('f')
      ->from('MyTravel\Core\Model\File', 'f')
      ->where($expr)
      ->setParameter(':type', 'image/%');
    $query = $qb->getQuery();
    return $query->getResult();
  }

  /**
   * Synchronize Timeline Items with Files.
   * Every Timeline Item is linked with an Image File found in db.
   * And every Image File in db is linked with a source in files directory.
   */
  public function sync() {
    // 1. Sync files with db
    $ctrlFile = new FileController();
    $ctrlFile->sync();
    // 2. Sync timeline items with files
    // - get images
    $imgFileList = $this->getImages();
    // - get timeline items
    $ctrlItem = new ItemController();
    $timelineList = $ctrlItem->getItemList();
    // Match with file
    foreach ($timelineList as $k => $item) {
      if (in_array($item->file, $imgFileList)) {
        $f = array_search($item->file, $imgFileList);
        unset($timelineList[$k], $imgFileList[$f]);
      }
    }
    // Add leftover files
    foreach ($imgFileList as $file) {
      $item = new TimelineItem();
      $item->setFile($file);
      $item->path = $ctrlItem->getUniquePath($item->path);
      Db::get()->persist($item);
    }
    // Remove leftover timeline items
    foreach ($timelineList as $item) {
      Db::get()->remove($item);
    }
    // Need to flush because we want the items used on page
    Db::get()->flush();
  }

}
