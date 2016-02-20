<?php

namespace tinyorm\test\persistence;

use tinyorm\persistence\DbDriver;
use tinyorm\persistence\ZHandlersocketDriver;
use tinyorm\test\PersistenceDriverTest;

class ZHandlersocketDriverTest extends PersistenceDriverTest {

    private $zClient;

    protected function isTestSkipped()
    {
        return !class_exists(\ZHandlersocket\Client::class);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->zClient = new \ZHandlersocket\Client();
    }



    /**
     * @return DbDriver
     */
    protected function createPersistenceDriver() {
        return new ZHandlersocketDriver($this->zClient, "test");
    }


}