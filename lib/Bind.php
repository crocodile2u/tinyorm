<?php
/**
 * Bind class.
 *
 * Values can be bound to statement either as strings (default PDO behavior)
 * or they can be bound using a particular type. Usually, binding as strings works fine.
 * However, there might be situations when you will need binding as integer/boolean etc.
 *
 * Just pass an Bind instance to Select:
 *
 * $select->where("id = ?", Bind::int(123));
 *
 * That will do the trick, and the variable will be bound as INT.
 */
namespace tinyorm;

class Bind {
    /**
     * @var int
     */
    private $type;
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     * @return Bind
     */
    static function int($value) {
        return new self(\PDO::PARAM_INT, (int) $value);
    }

    /**
     * @param mixed $value
     * @return Bind
     */
    static function bool($value) {
        return new self(\PDO::PARAM_BOOL, (bool) $value);
    }

    /**
     * @param mixed $value
     * @return Bind
     */
    static function string($value) {
        return new self(\PDO::PARAM_STR, (string) $value);
    }

    /**
     * @param mixed $value
     * @return Bind
     */
    static function float($value) {
        return new self(\PDO::PARAM_STR, (string) (float) $value);
    }

    /**
     * @param mixed $value
     * @return Bind
     */
    static function lob($value) {
        return new self(\PDO::PARAM_LOB, (string) (float) $value);
    }

    /**
     * @param int $type
     * @param mixed $value
     * @return Bind
     */
    private function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return int
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return (string) $this->value;
    }
}