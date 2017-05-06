<?php
/**
 * TxManager (Transaction Manager. Handles transactions which are run by database connections).
 * Takes care of nested transactions. Upon COMMIT, all the participating transactions are commited
 * (well, in case there's no nesting, otherwise the nesting level is decremented).
 * In case of ROLLBACK, all participating transactions are rolled back, for all registered connections.
 *
 * Created by PhpStorm.
 * User: vbolshov
 * Date: 29-12-15
 * Time: 19:33
 */

namespace tinyorm;


class TxManager
{
    /**
     * @var DbInterface[]
     */
    private $connections = [];

    private $depth = 0;

    function atomic($callback)
    {
        $this->begin();

        try {
            $ret = $callback();
            if (!$ret) {
                throw new \Exception(__METHOD__ . ": callback returned FALSE");
            }
            $this->commit();
            return $ret;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return $this
     */
    function begin()
    {
        $this->depth++;
        foreach ($this->connections as $connection) {
            if (!$connection->beginTransaction()) {
                $this->rollback();
                throw new \RuntimeException("Unable to begin transaction");
            }
        }
        return $this;
    }

    function commit() {
        $this->depth--;
        if ($this->depth < 0) {
            $this->depth = 0;
            throw new \LogicException("Cannot commit: no transaction was started");
        } elseif ($this->depth > 0) {
            return true;
        }
        foreach ($this->connections as $connection) {
            if ($connection->inTransaction() && !$connection->commit()) {
                $this->rollback();
                throw new \RuntimeException("Unable to commit transaction");
            }
        }
        return true;
    }
    public function inTransaction()
    {
        return $this->depth > 0;
    }
    function rollback() {
        foreach ($this->connections as $connection) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
        }
        $this->depth = 0;
    }

    /**
     * @param DbInterface $connection
     * @return $this
     */
    function registerConnection(DbInterface $connection) {
        $this->connections[$connection->getConnectionId()] = $connection;
        return $this;
    }

    function unregisterConnection(DbInterface $connection) {
        unset($this->connections[$connection->getConnectionId()]);
    }
}