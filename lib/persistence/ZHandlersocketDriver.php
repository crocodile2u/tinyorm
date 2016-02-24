<?php
/**
 * ZHandlersocketDriver: persistence driver for tinyorm entities, based on ZHandlersocket.
 * @see https://github.com/crocodile2u/zhandlersocket
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm\persistence;

use tinyorm\Entity;

class ZHandlersocketDriver implements Driver
{
    /**
     * @var \Zhandlersocket\Client
     */
    private $zClient;
    /**
     * @var string
     */
    private $dbName;

    function __construct(\Zhandlersocket\Client $client, $dbName)
    {
        $this->zClient = $client;
        $this->dbName = $dbName;
    }

    function setDbName($dbName)
    {
        $this->dbName = $dbName;
        return $this;
    }

    function find($id, Entity $proto)
    {
        $row = $this->getIndex($proto)->find($id);
        if ($row) {
            return $proto->importArray($row);
        }
    }

    /**
     * @param Entity $entity
     * @param int &$affectedRowCount
     * @return bool|int|string|Entity
     */
    function save(Entity $entity, &$affectedRowCount = null)
    {
        if ($entity->getPK()) {
            return $this->update($entity, $affectedRowCount);
        } else {
            return $this->insert($entity, $affectedRowCount);
        }
    }

    /**
     * @param Entity $entity
     * @param int &$affectedRowCount
     * @return Entity|bool
     */
    function update(Entity $entity, &$affectedRowCount = null)
    {
        if (!$entity->getPK()) {
            throw new \LogicException("Cannot update entity: PK is empty");
        }

        $this->getIndex($entity)->updateById($entity->getPK(), $entity->toArray());
        $affectedRowCount = 1;
        return $entity;
    }

    /**
     * @param Entity $entity
     * @param int &$affectedRowCount
     * @return Entity
     */
    function insert(Entity $entity, &$affectedRowCount = null)
    {
        if ($entity->getPK()) {
            throw new \LogicException("Cannot insert entity: PK is not empty");
        }

        $id = $this->getIndex($entity)->insert($entity->toArray());
        if ($id) {
            $affectedRowCount = 1;
            $entity->setPK($id);
            return $entity;
        } else {
            throw new \LogicException("INSERT: cannot get inserted entity ID");
        }
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    function delete(Entity $entity)
    {
        if (!$entity->getPK()) {
            throw new \LogicException("Cannot delete entity: PK is empty");
        }

        return $this->getIndex($entity)->deleteById($entity->id);
    }

    /**
     * @param Entity $proto
     * @retrun \Zhandlersocket\Index
     */
    protected function getIndex(Entity $proto)
    {
        return $this->zClient->getIndex(
            $this->dbName,
            $proto->getSourceName(),
            "PRIMARY",
            $proto->getColumns()
        );
    }
}