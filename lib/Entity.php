<?php
/**
 * Entity. Base class for entities managed by tinyorm.
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm;


abstract class Entity
{
    /**
     * @var array
     */
    private $data;
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
     * Entity constructor.
     * @param array $data
     */
    function __construct(array $data = [])
    {
        $this->data = array_merge($this->getDefaults(), $data);
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
}