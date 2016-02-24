<?php
/**
 * DbDriver: persistence driver for tinyorm entities, SQL/Db based.
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm\persistence;


use tinyorm\Db;
use tinyorm\Entity;

class DbDriver implements Driver
{
    /**
     * @var Db
     */
    private $db;

    function __construct(Db $db)
    {
        $this->db = $db;
    }

    function find($id, Entity $proto)
    {
        $sql = "SELECT * FROM {$proto->getSourceName()} WHERE {$proto->getPKName()} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $stmt->setFetchMode(\PDO::FETCH_INTO, $proto);
        return $stmt->fetch() ?: null;
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

        $data = $entity->toArray();
        $autoUpdatedCols = $entity->getAutoUpdatedCols(true);
        $sqlUpdate = [];
        foreach (array_keys($data) as $col) {
            if (isset($autoUpdatedCols[$col])) {
                continue;
            }
            $sqlUpdate[] = "$col = :$col";
        }
        $sql = "UPDATE {$entity->getSourceName()} SET " . join(", ", $sqlUpdate) .
            " WHERE {$entity->getPKName()} = :{$entity->getPKName()}";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        if (!$result) {
            throw new \RuntimeException("UDPATE entity: DB query failed (PK: {$entity->getPK()})");
        }
        $affectedRowCount = $stmt->rowCount();
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

        $data = $entity->toArray();
        $autoUpdatedCols = $entity->getAutoUpdatedCols(true);
        $keys = array_keys($data);
        $sqlValues = [];
        foreach ($keys as $col) {
            if (isset($autoUpdatedCols[$col])) {
                continue;
            }
            $sqlValues[] = ":$col";
        }
        $sql = "INSERT INTO {$entity->getSourceName()} (" . join(", ", $keys) . ") " .
            " VALUES (" . join(", ", $sqlValues) . ")";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        if (!$result) {
            throw new \RuntimeException("INSERT entity: DB query failed");
        }
        $affectedRowCount = $stmt->rowCount();
        if ($affectedRowCount) {
            $id = (int) $this->db->lastInsertId($entity->getSequenceName());
            if ($id) {
                $entity->setPK($id);
                return $entity;
            } else {
                throw new \LogicException("INSERT: cannot get lastInsertId");
            }
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

        $sql = "DELETE FROM {$entity->getSourceName()} " .
            " WHERE {$entity->getPKName()} = :{$entity->getPKName()}";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $entity->getPKName() => $entity->getPK(),
        ]);
        if (!$result) {
            throw new \RuntimeException("DELETE entity: DB query failed (PK: {$entity->getPK()})");
        }
        return (bool) $stmt->rowCount();
    }
}