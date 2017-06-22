<?php
/**
 * Db connection wrapper. Implements proxy object design pattern.
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm;


use tinyorm\log\LogInterface;
use tinyorm\persistence\DbDriver;

class Db implements DbInterface
{
    /**
     * @var TxManager
     */
    private static $defaultTxManager;
    /**
     * @var \PDO
     */
    private $pdo;
    /**
     * @var string
     */
    private $dsn;
    /**
     * @var string
     */
    private $user;
    /**
     * @var string
     */
    private $password;
    /**
     * @var array
     */
    private $options = [];

    private static $nextId = 1;

    private $id;

    private $txManager;

    private $shouldStartTransaction = 0;
    /**
     * @var LogInterface
     */
    private $debugLog;

    private $name;

    private $queryCount = 0;

    /**
     * Db constructor.
     * @param $dsn
     * @param null $user
     * @param null $password
     * @param array $options
     */
    function __construct($dsn, $user = null, $password = null, array $options = [])
    {
        $this->dsn = $dsn;
        $this->user = $user;
        $this->password = $password;
        $options[\PDO::ATTR_STATEMENT_CLASS] = [Statement::class, [$this]];
        $this->options = $options;
        $this->id = self::$nextId++;
        $this->queryCount = 0;
    }

    function getName()
    {
        if (null === $this->name) {
            $this->name = $this->autoName();
        }

        return $this->name;
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function __clone()
    {
        $this->pdo = null;
        $this->txManager = null;
        $this->shouldStartTransaction = 0;
        $this->id = self::$nextId++;
    }

    /**
     * @return DbDriver
     */
    function createPersistenceDriver()
    {
        return new DbDriver($this);
    }

    function setDebugLog(LogInterface $logger)
    {
        $this->debugLog = $logger;
    }

    public function beginTransaction()
    {
        $timer = $this->debugLog("Begin transaction");

        if (!$this->inTransaction()) {
            $this->debugLog("Not actually starting a transaction, only setting shouldStartTransaction flag to TRUE");
            $this->shouldStartTransaction = true;
        }

        $this->debugTimerEnd($timer, "Begin transaction");

        return true;
    }

    public function getConnectionId()
    {
        return $this->id;
    }

    public function commit()
    {
        $timer = $this->debugLog("Commit");

        if ($this->shouldStartTransaction) {
            $this->debugLog("Not actually commiting the transaction, because the real DB transaction was not started");
            $this->shouldStartTransaction = false;
            return true;
        }

        if ($this->inTransaction()) {
            $this->debugLog("About to commit transaction");
            $ret = $this->getPdo()->commit();
            $this->queryCount++;
            $this->debugTimerEnd($timer, "Transaction commited, commit() returned " . (int) $ret);
            return $ret;
        } else {
            throw new \LogicException("Cannot commit: transaction not started");
        }
    }

    public function errorCode()
    {
        return $this->getPdo()->errorCode();
    }

    public function errorInfo()
    {
        return $this->getPdo()->errorInfo();
    }

    public function exec($statement)
    {
        $timer = $this->debugLog("Exec: $statement");
        $this->beginTransactionIfNeeded();
        $ret = $this->getPdo()->exec($statement);
        $this->debugTimerEnd($timer, "exec(): " . (int) $ret);
        $this->queryCount++;
        return $ret;
    }

    public function getAttribute($attribute)
    {
        return $this->getPdo()->getAttribute($attribute);
    }

    public function inTransaction()
    {
        $ret = $this->getPdo()->inTransaction();
        $this->debugLog("In transaction? " . (int) $ret);
        return $ret;
    }

    public function lastInsertId($name = null)
    {
        $timer = $this->debugLog("lastInsertId");
        $this->beginTransactionIfNeeded();
        $ret = $this->getPdo()->lastInsertId($name);
        $this->queryCount++;
        $this->debugTimerEnd($timer, "lastInsertId(): " . (int) $ret);
        return $ret;
    }

    public function prepare($statement, array $driver_options = array())
    {
        $timer = $this->debugLog("prepare");
        $this->beginTransactionIfNeeded();
        /** @var Statement $ret */
        $ret = $this->getPdo()->prepare($statement, $driver_options);
        $this->queryCount++;
        $this->debugTimerEnd($timer, "prepare(): " . $statement);
        return $ret;
    }

    public function query($statement)
    {
        $timer = $this->debugLog("query");
        $this->beginTransactionIfNeeded();
        $ret = $this->getPdo()->query($statement);
        $this->queryCount++;
        $this->debugTimerEnd($timer, "query($statement): " . ($ret ? "SUCCESS" : "FAILURE"));
        return $ret;
    }

    public function quote($string ,$parameter_type = \PDO::PARAM_STR )
    {
        return $this->getPdo()->quote($string, $parameter_type);
    }

    public function rollBack()
    {
        $timer = $this->debugLog("About to rollBack");
        if ($this->shouldStartTransaction) {
            $this->shouldStartTransaction = 0;
            $this->debugTimerEnd($timer, "Performing fake rollback (real transaction was not started)");
            return true;
        } elseif ($this->getPdo()->inTransaction()) {
            $ret = $this->getPdo()->rollBack();
            $this->queryCount++;
            $this->debugTimerEnd($timer, "Transaction rolled back: " . (int) $ret);
            return $ret;
        } else {
            throw new \LogicException("Unable to rollback: transaction not started");
        }
    }

    public function setAttribute($attribute, $value)
    {
        return $this->getPdo()->setAttribute($attribute, $value);
    }

    public static function getDefaultTxManager()
    {
        if (self::$defaultTxManager === null) {
            self::$defaultTxManager = new TxManager();
        }
        return self::$defaultTxManager;
    }
    public static function setDefaultTxManager(TxManager $manager)
    {
        self::$defaultTxManager = $manager;
    }

    /**
     * @return TxManager
     */
    public function getTxManager()
    {
        if (null === $this->txManager) {
            $this->txManager = self::getDefaultTxManager()->registerConnection($this);
        }
        return $this->txManager;
    }

    /**
     * @param TxManager $manager
     */
    public function setTxManager(TxManager $manager)
    {
        if (null !== $this->txManager) {
            $this->txManager->unregisterConnection($this);
        }
        $this->txManager = $manager->registerConnection($this);
    }

    /**
     * Begin a transaction managed by transaction manager.
     * You are encouraged to use beginMultiTransaction() or multiAtomic() for transactions.
     *
     * @return TxManager
     */
    public function beginMultiTransaction()
    {
        return $this->getTxManager()->begin();
    }

    /**
     * @param $callback
     * @return mixed
     * @throws \Exception
     */
    public function multiAtomic($callback)
    {
        return $this->getTxManager()->atomic($callback);
    }

    /**
     * @return int
     */
    public function getQueryCount()
    {
        return $this->queryCount;
    }

    public function resetQueryCount()
    {
        $this->queryCount = 0;
    }


    /**
     * @return \PDO
     */
    public function getPdo()
    {
        if (null === $this->pdo) {
            $timer = $this->debugLog("Establish PDO connection");
            $this->pdo = new \PDO($this->dsn, $this->user, $this->password, $this->options);
            $this->debugTimerEnd($timer, "Connection established");
        }
        return $this->pdo;
    }

    public function emulateCommit()
    {
        $this->shouldStartTransaction = false;
    }

    public function emulateRollback()
    {
        $this->shouldStartTransaction = false;
    }

    public function beginTransactionIfNeeded()
    {
        if ($this->shouldStartTransaction) {
            $this->debugLog("Actually starting a transaction");
            $this->getPdo()->beginTransaction();
            $this->queryCount++;
            $this->shouldStartTransaction = false;
        }
    }

    private function debugLog($message)
    {
        if (null !== $this->debugLog) {
            $this->debugLog->write("{$this->getName()} " . $message);
            return $this->debugTimerStart();
        }
    }

    private function debugTimerStart()
    {
        if (null !== $this->debugLog) {
            return microtime(true);
        }
    }

    private function debugTimerEnd($timestamp, $message = null)
    {
        if (null !== $this->debugLog) {
            $ret = ceil((microtime(true) - $timestamp) * 1000);
            if ($message) {
                $this->debugLog->write($message);
            }
            $this->debugLog->write("TIMER {$this->getName()} $message: $ret ms elapsed");
            return $ret;
        }
    }

    private function autoName()
    {
        return $this->user
            ? ($this->user . ":***@" . $this->dsn)
            : $this->dsn;
    }
}