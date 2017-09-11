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

  public function getStageLocations(int $stage, int $weight, $limit = 0) {
    $qb = Db::get()->createQueryBuilder();
    $expr = $qb->expr()->andX(
      $qb->expr()->gte('l.weight', ':weight'), $qb->expr()->eq('l.stage', ':stage')
    );
    $qb
      ->select('l')
      ->from('\MyTravel\Location\Model\Location', 'l')
      ->where($expr)
      ->setParameter(':stage', $stage)
      ->setParameter(':weight', $weight)
      ->orderBy('l.weight', 'ASC');
    // Optionally set limit
    if (!empty($limit)) {
      $qb->setMaxResults($limit);
    }
    return $qb->getQuery()->getResult();
  }

  /**
   * Synchronize GPX files with Locations.
   * Keeps track of already read Files in application saveState.
   * Only reads unread files once,
   * because there is a one to many relation between GPX files and locations.
   * Each GPX file represents a new stage.
   */
  public function sync() {
    // 1. Sync files
    $ctrlFile = new FileController();
    $ctrlFile->syncSources();
    // 2. Get highest existing stage number.
    // Take this straight from db since stages can be added manually.
    $lastStage = $this->getLastStage();
    // 3. Get savedState
    $ctrlState = SavedStateController::create();
    $savedState = $ctrlState->get(self::STATESYNC);
    // Detach savedState skipping flushes
    Db::get()->detach($savedState);
    // 4. Add any locations from new files to subsequent stages.
    // - path is expected to be subpath of files directory
    //   anything else can be considered invalid anyway
    $path = str_replace(Config::get()->directories['files'] . '/', '', Config::get()->directories['gpx']);
    $files = $ctrlFile->getFiles('application/xml', $path);
    foreach ($files as $file) {
      $duplicateCheck = in_array($file->id, $savedState->get('readFiles') ?? array());
      if ($duplicateCheck) {
        continue;
      }
      $reader = new GpxReader($file);
      $reader->saveGpxAsLocations( ++$lastStage);
      $savedState->add('readFiles', $file->id);
    }
    Db::get()->merge($savedState);
    // 5. Flush
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
