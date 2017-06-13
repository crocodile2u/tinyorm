<?php
/**
 * Created by PhpStorm.
 * User: vbolshov
 * Date: 29-12-15
 * Time: 19:30
 */

namespace tinyorm;


interface DbInterface
{
    function __construct($dsn, $user = null, $password = null, array $options = []);

    /**
     * @return int|string
     */
    public function getConnectionId();

    /**
     * @return bool
     */
    public function beginTransaction();

    /**
     * @return bool
     */
    public function commit();

    /**
     * Called when commiting a multi-connection-transaction, for connections that did not have a need to actually
     * start a transaction.
     * @return mixed
     */
    public function emulateCommit();


    /**
     * Called when rolling back a multi-connection-transaction, for connections that did not have a need to actually
     * start a transaction.
     * @return mixed
     */
    public function emulateRollback();

    /**
     * @return int
     */
    public function errorCode();

    /**
     * @return string
     */
    public function errorInfo();

    /**
     * @param string $statement
     * @return bool
     */
    public function exec($statement);

    /**
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute($attribute);

    /**
     * @return bool
     */
    public function inTransaction();

    /**
     * @param string $name
     * @return int
     */
    public function lastInsertId($name = null);

    /**
     * @param string $statement
     * @param array $driver_options
     * @return \PDOStatement
     */
    public function prepare($statement, array $driver_options = array());

    /**
     * @param $statement
     * @return bool
     */
    public function query($statement);

    /**
     * @param string $string
     * @param int $parameter_type
     * @return string
     */
    public function quote($string ,$parameter_type = PDO::PARAM_STR );

    /**
     * @return bool
     */
    public function rollBack();

    /**
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($attribute, $value);

    /**
     * @return TxManager
     */
    public static function getDefaultTxManager();

    /**
     * @param TxManager $manager
     * @return mixed
     */
    public static function setDefaultTxManager(TxManager $manager);

    /**
     * @return TxManager
     */
    public function getTxManager();

    /**
     * @param TxManager $manager
     * @return $this
     */
    public function setTxManager(TxManager $manager);
    /**
     * @return TxManager
     */
    public function beginMultiTransaction();
}