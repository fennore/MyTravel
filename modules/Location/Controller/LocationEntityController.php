<?php

namespace MyTravel\Location\Controller;

use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;
use MyTravel\Core\Controller\Config;
use MyTravel\Core\Controller\Db;
use MyTravel\Core\Controller\FileController;
use MyTravel\Core\Controller\SavedStateController;
use MyTravel\Location\GpxReader;

class LocationEntityController {
  const STATESYNC = 943;

  /**
   * Get the last recorded stage.
   * @return int
   */
  private function getLastStage() {
    $qb = Db::get()->createQueryBuilder();
    $qb
      ->select('MAX(l.stage) AS lastStage')
      ->from('MyTravel\Location\Model\Location', 'l');
    return (int) $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
  }

  /**
   * Synchronize GPX files with Locations.
   * Keeps track of already read Files in application saveState.
   * Only reads unread files once,
   * because there is a one to many relation between GPX files and locations.
   * Each GPX file represents a new stage.
   */
  public function sync() {
    $persistState = false;
    // 1. Sync files
    $ctrlFile = new FileController();
    $ctrlFile->syncSources();
    // 2. Get highest existing stage number.
    // Take this straight from db since stages can be added manually.
    $lastStage = $this->getLastStage();
    // 3. Get savedState
    $ctrlState = new SavedStateController();
    $savedState = $ctrlState->get(self::STATESYNC);
    // Detach savedState because we are gonna write some stuff to db first!
    Db::get()->detach($savedState);
    $newState = (object) $savedState->getState();
    if (empty($newState)) {
      $persistState = true;
    }
    // 4. Add any locations from new files to subsequent stages.
    $files = $ctrlFile->getFiles('application/xml');
    foreach ($files as $file) {
      $duplicateCheck = in_array($file->id, $newState->readFiles ?? array());
      $directoryCheck = Config::get()->directories['files'] . '/' . $file->path === Config::get()->directories['gpx'];
      if ($duplicateCheck || !$directoryCheck) {
        continue;
      }
      $reader = new GpxReader($file);
      $reader->saveGpxAsLocations(++$lastStage);
      $newState->readFiles[] = $file->id;
    }
    // 5. Save state
    // Merge savedState
    $savedState->setState($newState);
    if ($persistState) {
      Db::get()->persist($savedState);
    } else {
      Db::get()->merge($savedState);
    }
    Db::get()->flush();
    Db::get()->clear();
  }

  /**
   * JSON API output
   * @param Request $request
   */
  public function output(Request $request) {

  }

  /**
   * Create
   * @param Request $request
   */
  public function create(Request $request) {

  }

  /**
   * Update
   * @param Request $request
   */
  public function update(Request $request) {

  }

  /**
   * Delete
   * @param Request $request
   */
  public function delete(Request $request) {

  }

}
