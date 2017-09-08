<?php

namespace MyTravel\Core\Controller;

use ErrorException;
use Symfony\Component\Finder\Finder;
use MyTravel\Core\Model\File;
use Doctrine\ORM\Query\Expr;

class FileController {

  /**
   * Get all Files from database,
   * optionally filtered by parameter.
   * @param string|array $mimeMatch
   * @return Result array
   */
  private function getFiles($mimeMatch) {
    $qb = Db::get()->createQueryBuilder();
    if (is_string($mimeMatch)) {
      $expr = $qb
        ->expr()
        ->like('f.type', ':type');
    } else if (is_array($mimeMatch)) {
      $expr = $qb
        ->expr()
        ->in('f.type', ':type');
    }
    $qb
      ->select('f')
      ->from('MyTravel\Core\Model\File', 'f')
      ->where($expr)
      ->setParameter(':type', $mimeMatch);
    $query = $qb->getQuery();
    return $query->getResult();
  }

  /**
   * Full source files synchronization with SourceItems of one type.
   * @param string $itemTypeClass SourceItem traited class.
   * @param string|array $fileFilter filter for file MIME type.
   * @throws ErrorException
   */
  public function sync($itemTypeClass) {
    $uses = class_uses($itemTypeClass);
    if ($uses === false || !in_array('MyTravel\Core\ImageSource', $uses)) {
      throw new ErrorException('Files sync should only be called for Items using the SourceItem trait.');
    }
    // 1. Sync files with db
    $removedIdList = $this->syncSources();
    // 2. Clean ghost files
    $this->cleanGhostFiles($removedIdList, $itemTypeClass);
    // 3. Source Items with files
    // - get Files
    $fileList = $this->getFiles($itemTypeClass::MIMEMATCH);
    // - get Source Items
    $ctrlItem = new ItemController($itemTypeClass);
    $itemList = $ctrlItem->getItemList();
    // Match with file
    foreach ($itemList as $k => $item) {
      if (in_array($item->file, $fileList)) {
        $f = array_search($item->file, $fileList);
        unset($itemList[$k], $fileList[$f]);
      }
    }
    // Add leftover files
    foreach ($fileList as $file) {
      $item = new $itemTypeClass();
      $item->setFile($file);
      $item->path = $ctrlItem->getUniquePath($item->path);
      Db::get()->persist($item);
    }
    // Remove leftover items
    foreach ($itemList as $item) {
      Db::get()->remove($item);
    }
    // Need to flush because we want the items used on page
    Db::get()->flush();
  }

  /**
   * Synchronizes files in directory with database.
   * @return array List of File ids that got deleted.
   */
  public function syncSources() {
    // Select all files from db
    $qb = Db::get()->createQueryBuilder();
    $dbFiles = $qb
      ->select('f')
      ->from('MyTravel\Core\Model\File', 'f')
      ->getQuery()
      ->getArrayResult();
    $dbFileSources = array_column($dbFiles, 'source', 'id');
    // Fetch all files from files directory
    $dirFiles = Finder::create()
      ->files()
      ->in(Config::get()->directories['files']);
    $batchSize = 50;
    $i = 0;
    foreach ($dirFiles as $splFile) {
      $id = array_search($splFile->getRelativePathname(), $dbFileSources);
      if ($id !== false) {
        // skip already recorded files
        unset($dbFileSources[$id]);
        continue;
      }
      ++$i;
      $file = new File($splFile);
      Db::get()->persist($file);
      if (($i % $batchSize) === 0) {
        Db::get()->flush();
        Db::get()->clear(); // Detaches all objects from Doctrine!
      }
    }
    // Remove orphan records
    foreach ($dbFileSources as $id => $data) {
      $df = new File();
      $df->id = $id;
      $file = Db::get()->merge($df);
      Db::get()->remove($file);
    }
    Db::get()->flush(); //Persist objects that did not make up an entire batch
    Db::get()->clear();
    //
    return array_keys($dbFileSources);
  }

  /**
   * Remove SourceItem links with deleted Files
   * @param array $removedIdList List of ids of deleted Files
   * @param string $itemTypeClass Item Class
   */
  private function cleanGhostFiles($removedIdList, $itemTypeClass) {
    $qb = Db::get()->createQueryBuilder();
    $expr = $qb
      ->expr()
      ->in('t.file', ':ids');
    $qb
      ->update($itemTypeClass, 't')
      ->set('t.file', ':setnull')
      ->where($expr)
      ->setParameter(':setnull', null)
      ->setParameter(':ids', $removedIdList);
    $query = $qb->getQuery();
    $query->execute();
  }

}
