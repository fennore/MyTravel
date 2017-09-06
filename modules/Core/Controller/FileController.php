<?php

namespace MyTravel\Core\Controller;

use Symfony\Component\Finder\Finder;
use MyTravel\Core\Model\File;
use MyTravel\Core\Controller\App;
use MyTravel\Core\CoreEvents;
use MyTravel\Core\Event\IdListEvent;

class FileController {
  public function sync() {
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
    // dispatch actions upon removed file items
    $event = new IdListEvent(array_keys($dbFileSources));
    App::event()->dispatch(CoreEvents::RMFILES, $event);
    Db::get()->flush(); //Persist objects that did not make up an entire batch
    Db::get()->clear();
  }

}
