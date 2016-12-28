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
     * A limit must be specified when fetching from HS, so this is needed by findAllByColumn().
     * Well, if you're trying to fetch more then 2 ** 31, you're undoubtedly in trouble anyway.
     */
    const FIND_ALL_LIMIT = 2147483647;
    /**
     * @var \Zhandlersocket\Client
     */
    private $zClient;
    /**
     * @var string
     */
    private $dbName;

    /**
     * ZHandlersocketDriver constructor.
     * @param \Zhandlersocket\Client $client
     * @param string $dbName
     */
    function __construct(\Zhandlersocket\Client $client, $dbName)
    {
        $this->zClient = $client;
        $this->dbName = $dbName;
    }

    /**
     * @param string $dbName
     * @return $this
     */
    function setDbName($dbName)
    {
        $this->dbName = $dbName;
        return $this;
    }

    /**
     * @param int $id
     * @param Entity $proto
     * @return $this
     */
    function find($id, Entity $proto)
    {
        $row = $this->getIndex($proto)->find($id);
        if ($row) {
            return $proto->importArray($row);
        }
    }
    /**
     * Find all entities with column = value.
     *
     * For ZHandlersocketDriver, you have to specify INDEX name in $column arg, not the column name.
     * Obviously, the column must have an index on it to be searchable via this driver.
     *
     * Because of the limitations of HS,
     *
     * @param int $id
     * @return Entity[]
     */
    function findAllByColumn($column, $value, Entity $proto)
    {
        $index = $this->getIndex($proto, $column);
        $where = $index->createWhereClause("=", [$value])->setLimit(self::FIND_ALL_LIMIT);
        foreach ($this->getIndex($proto, $column)->findByWhereClause($where) as $row) {
            yield (clone $proto)->importArray($row);
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
     * @param string $column
     * @param int $amount
     * @return bool
     */
    function increment(Entity $entity, $column, $amount = 1)
    {
        $this->getIndex($entity)->incrementById($entity->getPK(), [$column => $amount]);
        $entity->$column += $amount;
        return true;
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
     * @param string $index
     * @retrun \Zhandlersocket\Index
     */
    protected function getIndex(Entity $proto, $index = "PRIMARY")
    {
        return $this->zClient->getIndex(
            $this->dbName,
            $proto->getSourceName(),
            $index,
            $proto->getColumns()
        );
    }
}