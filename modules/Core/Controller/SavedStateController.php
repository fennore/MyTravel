<?php

namespace MyTravel\Core\Controller;

use MyTravel\Core\Model\SavedState;

class SavedStateController {

  /**
   * Get SavedState matching given key parameter
   * @param int $key State identifier
   * @return \MyTravel\Core\Model\SavedState Persistent SavedState object
   */
  public function get($key) {
    $qb = Db::get()->createQueryBuilder();
    $qb
      ->select('s')
      ->from('MyTravel\Core\Model\SavedState', 's')
      ->where($qb->expr()->eq('s.key', ':key'))
      ->setParameter(':key', $key);
    $savedState = $qb->getQuery()->getOneOrNullResult() ?? new SavedState($key);
    Db::get()->persist($savedState);
    return $savedState;
  }

}
