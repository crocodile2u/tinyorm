<?php

namespace tinyorm\test\persistence;

use tinyorm\persistence\DbDriver;
use tinyorm\test\PersistenceDriverTest;

class DbDriverTest extends PersistenceDriverTest {
    /**
     * @return DbDriver
     */
    protected function createPersistenceDriver() {
        return new DbDriver($this->connection);
    }
}