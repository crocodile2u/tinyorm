<?php
/**
 * Created by PhpStorm.
 * User: vbolshov
 * Date: 1-2-16
 * Time: 10:48
 */

namespace tinyorm\persistence;

use tinyorm\Entity;

interface Driver
{
    /**
     * @param int $id
     * @return Entity
     */
    function find($id, Entity $proto);
    /**
     * @param string $column
     * @param mixed $value
     * @param int $limit
     * @return Entity[]
     */
    function findAllByColumn($column, $value, Entity $proto, $limit = null);
    /**
     * @param string $column
     * @param mixed $value
     * @return Entity
     */
    function findByColumn($column, $value, Entity $proto);
    /**
     * @param Entity $entity
     * @return Entity
     */
    function save(Entity $entity, &$affectedRowCount = null);
    /**
     * @param Entity $entity
     * @return Entity
     */
    function increment(Entity $entity, $column, $amount = 1);
    /**
     * @param Entity $entity
     * @return bool
     */
    function update(Entity $entity, &$affectedRowCount = null);
    /**
     * @param Entity $entity
     * @return int the inserted entity ID.
     */
    function insert(Entity $entity, &$affectedRowCount = null);
    /**
     * @param Entity $entity
     * @return bool
     */
    function delete(Entity $entity);
}