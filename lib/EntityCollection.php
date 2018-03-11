<?php

namespace tinyorm;

class EntityCollection extends \ArrayObject
{
    public function __construct(iterable $input = [], $flags = 0, $iteratorClass = \ArrayIterator::class)
    {
        parent::__construct([], $flags, $iteratorClass);
        foreach ($input as $entity) {
            $this->doAppend($entity);
        }
    }

    /**
     * @param TxManager|null $txManager
     * @return mixed
     * @throws \Exception
     */
    public function save(TxManager $txManager = null)
    {
        $txManager = $txManager ?? Db::getDefaultTxManager();
        return $txManager->atomic(function() {
            foreach ($this as $entity) {
                $entity->save();
            }
            return true;
        });
    }

    /**
     * @param TxManager|null $txManager
     * @return mixed
     * @throws \Exception
     */
    public function delete(TxManager $txManager = null)
    {
        $txManager = $txManager ?? Db::getDefaultTxManager();
        return $txManager->atomic(function() {
            foreach ($this as $entity) {
                $entity->delete();
            }
            return true;
        });
    }

    public function append($entity)
    {
        $this->doAppend($entity);
    }

    protected function doAppend(Entity $entity)
    {
        parent::append($entity);
    }

    public function offsetSet($offset, $value)
    {
        $this->doOffsetSet($offset, $value);
    }

    protected function doOffsetSet($offset, Entity $entity)
    {
        parent::offsetSet($offset, $entity);
    }
}