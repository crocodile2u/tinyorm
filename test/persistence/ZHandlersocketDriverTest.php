<?php

namespace tinyorm\test\persistence;

use tinyorm\persistence\ZHandlersocketDriver;
use tinyorm\test\PersistenceDriverTest;

class ZHandlersocketDriverTest extends PersistenceDriverTest {

    protected function isTestSkipped()
    {
        return !class_exists(\ZHandlersocket\Client::class);
    }

    /**
     * @return ZHandlersocketDriver
     */
    protected function createPersistenceDriver() {
        $client = new \ZHandlersocket\Client();
        $client->setLogger(new \Zhandlersocket\DebugLogger());
        return new ZHandlersocketDriver($client, "test");
    }


}