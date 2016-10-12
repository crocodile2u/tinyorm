<?php
/**
 * Entity. Base class for entities managed by tinyorm.
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm;

use tinyorm\persistence\Driver;

abstract class Entity
{
    /**
     * @var \tinyorm\persistence\Driver
     */
    static private $defaultPersistenceDriver;
    /**
     * @var array
     */
    private $data = [];
    /**
     * @var string
     */
    protected $sourceName;
    /**
     * @var string
     */
    protected $sequenceName;
    /**
     * @var string
     */
    protected $pkName = "id";
    /**
     * These fields will not be inserted/updated, the DB backend is responsible for them.
     *
     * @var string[]
     */
    protected $autoUpdatedCols = [];
    /**
     * Set default persistence driver to be used for find/save/delete operations.
     * @param Driver $driver
     */
    static function setDefaultPersistenceDriver(Driver $driver)
    {
        self::$defaultPersistenceDriver = $driver;
    }

    static function unsetDefaultPersistenceDriver()
    {
        self::$defaultPersistenceDriver = null;
    }

    /**
     * @param $id
     * @param Driver|null $driver
     * @return Entity
     */
    static function find($id, Driver $driver = null)
    {
        return self::resolvePersistenceDriver($driver)->find($id, new static());
    }

    /**
     * @param Driver $explicit
     * @return Driver
     */
    static function resolvePersistenceDriver(Driver $explicit = null)
    {
        $driver = (null === $explicit) ? self::$defaultPersistenceDriver : $explicit;
        if (null === $driver) {
            throw new \LogicException("Persistence driver not specified as argument 2 and default is not set");
        }
        return $driver;
    }

    /**
     * Entity constructor.
     * @param array $data
     */
    function __construct(array $data = [])
    {
        // the 2nd arg to array_merge() is $this->data:
        // when PDO performs fetching in mode PDO::FETCH_CLASS, the instance
        // is created, populated with properties, and only after that the
        // contructor is called. So, $this->data may already contain some data.
        $this->data = array_merge($this->getDefaults(), $this->data, $data);
    }

    /**
     * @param Driver|null $driver
     * @return Entity
     */
    function save(Driver $driver = null)
    {
        return self::resolvePersistenceDriver($driver)->save($this);
    }

    /**
     * @param Driver|null $driver
     * @return Entity
     */
    function increment($column, $amount = 1, Driver $driver = null)
    {
        return self::resolvePersistenceDriver($driver)->increment($this, $column, $amount);
    }

    /**
     * @param Driver|null $driver
     * @return bool
     */
    function delete(Driver $driver = null)
    {
        return self::resolvePersistenceDriver($driver)->delete($this);
    }

    /**
     * @return string[]
     */
    function getColumns()
    {
        return array_keys($this->getDefaults());
    }

    /**
     * @return string
     */
    function getSourceName()
    {
        return $this->sourceName;
    }

    /**
     * @return string
     */
    function getSequenceName()
    {
        return $this->sequenceName;
    }

    /**
     * @return string
     */
    function getPKName()
    {
        return $this->pkName;
    }

    /**
     * @return string
     */
    function getPK()
    {
        return $this->{$this->getPKName()};
    }

    /**
     * @return string
     */
    function setPK($value)
    {
        $this->__set($this->getPKName(), $value);
    }

    /**
     * @return string[]
     */
    function getAutoUpdatedCols($flipped = false)
    {
        return $flipped ? array_flip($this->autoUpdatedCols) : $this->autoUpdatedCols;
    }

    /**
     * @return array
     */
    abstract function getDefaults();

    /**
     * @param array $data
     * @return $this
     */
    function importArray(array $data)
    {
        $this->data = array_intersect_key(array_merge($this->data, $data), $this->data);
        return $this;
    }

    /**
     * @return array
     */
    function toArray()
    {
        return array_intersect_key($this->data, $this->getDefaults());
    }

    function __get($name)
    {
        return $this->data[$name];
    }

    function __set($name, $value)
    {
        return $this->data[$name] = $value;
    }

    function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }
}
