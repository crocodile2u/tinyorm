<?php

namespace library;

use tinyorm\Db;
use tinyorm\persistence\DbDriver;

class Registry {
    /**
     * @var array
     */
    static $config;
    /**
     * @var Db
     */
    static $db;
    /**
     * @var DbDriver
     */
    static $persistenceDriver;

    static function loadConfig(array $config)
    {
        self::$config = $config;
    }

    /**
     * @return Db
     */
    static function db()
    {
        if (null === self::$db) {
            self::$db = new Db(
                self::$config["db.dsn"],
                self::$config["db.user"],
                self::$config["db.password"]
            );
        }
        return self::$db;
    }

    /**
     * @return DbDriver
     */
    static function persistenceDriver()
    {
        if (null === self::$persistenceDriver) {
            self::$persistenceDriver = new DbDriver(self::db());
        }
        return self::$persistenceDriver;
    }
}