<?php

namespace MyTravel\Core\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for Items
 * Can be used across item types
 *
 * @todo implement proper input validation
 */
class ItemController {

  private $classCall = 'MyTravel\Core\Model\Item';

  public function __construct($type = null) {
    if (!isset($type)) {
      $type = App::get()
        ->getRequest()
        ->attributes
        ->get('_type');
    }
    if (isset($type) && class_exists($type, true)) {
      $this->classCall = $type;
    }
  }
  /**
   * @todo collect into 1 throw,
   * needs to be of a custom exception form to catch and display as message.
   * @param Request $request
   * @throws Exception
   */
  public function validateItemInput(Request $request) {
    // Remove id, type, created, updated, link, path from ParameterBag
    $immutable = array('id', 'type', 'created', 'updated', 'link', 'path');
    array_map(array($request->request, 'remove'), $immutable);
    $newTitle = $request->request->get('title');
    if (empty($newTitle)) {
      throw new Exception('Item title cannot be empty');
    }
  }

  public function getItemByTitle($pathTitle) {
    // Preparing query
    $type = strtolower((new \ReflectionClass($this->classCall))->getShortName());
    $qb = Db::get()->createQueryBuilder();
    $expr = $qb
      ->expr()
      ->andX(
      $qb->expr()->eq('i.path', ':path'), $qb->expr()->eq('i.status', ':status')
    );

    $qb
      ->select('i')
      ->from($this->classCall, 'i')
      ->where($expr)
      ->setParameter(':status', 1)
      ->setParameter(':path', $type . '/' . $pathTitle);
    // Execute query
    $query = $qb->getQuery();
    return $query->getSingleResult();
  }

  public function getUniquePath($pathTitle) {
    $qb = Db::get()->createQueryBuilder();
    $expr = $qb
      ->expr()
      ->like('i.path', ':path')
    ;

    $qb
      ->select('i.path')
      ->from($this->classCall, 'i')
      ->where($expr)
      ->setParameter(':path', $pathTitle . '%');
    // Execute query
    $query = $qb->getQuery();
    $paths = array_column($query->getArrayResult(), 'path');
    if (empty($paths)) {
      return $pathTitle;
    }
    $i = 0;
    do {
      $newPath = $pathTitle . '-' . $i++;
    } while (in_array($newPath, $paths));
    return $newPath;
  }

  public function getItemList($offset = 0, $length = 0) {
    // Preparing query
    $qb = Db::get()->createQueryBuilder();
    $expr = $qb
      ->expr()
      ->andX(
      $qb->expr()->eq('i.status', ':status')
    );

    $qb
      ->select('i')
      ->from($this->classCall, 'i')
      ->where($expr)
      ->setParameter(':status', 1)
      ->orderBy('i.weight', 'ASC')
      ->addOrderBy('i.created', 'ASC')
      ->setFirstResult($offset);
    if (!empty($length)) {
      $qb->setMaxResults($length);
    }
    // Execute query
    $query = $qb->getQuery();
    return $query->getResult();
  }

  /**
   * Get one or more items in paging format,
   * through json API
   * @param Request $request
   * @return type
   */
  public function output(Request $request) {
    return $this->getItemList(
      $request->attributes->get('offset'), $request->attributes->get('limit')
    );
  }

  public function create(Request $request) {
    $this->validateItemInput($request);
    $item = new $this->classCall($request->request->all());
    $item->path = $this->getUniquePath($item->path);
    Db::get()->persist($item);
    // Need to flush because we want the item ID here
    Db::get()->flush();

    return $item;
  }

  public function update(Request $request) {
    $this->validateItemInput($request);
    
    $item = Db::get()
      ->find(
        $this->classCall, $request->attributes->get('id')
    );
    $oldPath = $item->path;
    $item->update($request->request->all());
    if ($oldPath !== $item->path) {
      $item->path = $this->getUniquePath($item->path);
    }
    Db::get()->persist($item);
    return $item;
  }

  public function delete(Request $request) {
    $item = Db::get()
      ->find(
      $this->classCall, $request->attributes->get('id')
    );
    Db::get()->remove($item);
    return $item;
  }

}
