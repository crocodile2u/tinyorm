<?php
/**
 * Copyright (c) 2004-2015, EMESA BV
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are PROHIBITED without prior written permission from
 * the author. This product may NOT be used anywhere and on any computer
 * except the server platform of EMESA BV. If you received this code
 * accidentally and without intent to use it, please report this
 * incident to the author by email.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT
 * OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */
/**
 * ${DESCRIPTION}
 *
 * @author Victor Bolshov <victor.bolshov@emesa.nl>
 * @phpcs
 */


namespace tinyorm\test;


use tinyorm\Db;

class TxManagerTest extends BaseTestCase
{
    /**
     * @var Db
     */
    protected $connection2;
    protected function setUp()
    {
        parent::setUp();
        $this->connection2 = clone $this->connection;
    }

    function testGroupCommit()
    {
        $txm = new \tinyorm\TxManager();
        $txm->registerConnection($this->connection)
            ->registerConnection($this->connection2);

        $result = $txm->atomic(function () {
            $this->connection->exec("INSERT INTO test (c_unique) VALUES ('val1')");
            $this->connection2->exec("INSERT INTO test (c_unique) VALUES ('val2')");
            return true;
        });

        $this->assertEquals(true, $result);
        // 3: BEGIN; INSERT; COMMIT;
        $this->assertEquals(3, $this->connection->getQueryCount());
        $this->assertEquals(3, $this->connection2->getQueryCount());
        $this->assertRowCount("test", 2);
    }

    function testGroupCommitOnlyAffectInstancesParticipatingInTransaction()
    {
        $txm = new \tinyorm\TxManager();
        $txm->registerConnection($this->connection)
            ->registerConnection($this->connection2);

        $result = $txm->atomic(function () {
            $this->connection->exec("INSERT INTO test (c_unique) VALUES ('val1')");
            return true;
        });

        $this->assertEquals(true, $result);
        // 3: BEGIN; INSERT; COMMIT;
        $this->assertEquals(3, $this->connection->getQueryCount());
        // 0: the 2nd connection should not issue a single query.
        $this->assertEquals(0, $this->connection2->getQueryCount());
        $this->assertRowCount("test", 1);
    }

    function testGroupRollback()
    {
        $txm = new \tinyorm\TxManager();
        $txm->registerConnection($this->connection)
            ->registerConnection($this->connection2);

        try {
            $txm->atomic(function () {
                $this->connection->exec("INSERT INTO test (c_unique) VALUES ('val1')");
                $this->connection2->exec("INSERT INTO test (c_unique) VALUES ('val2')");
                throw new \Exception("TEST");
                return true;
            });
            $this->fail("Expected exception throw");
        } catch (\Exception $e) {
            // 3: BEGIN; INSERT; ROLLBACK;
            $this->assertEquals(3, $this->connection->getQueryCount());
            $this->assertEquals(3, $this->connection2->getQueryCount());
            $this->assertRowCount("test", 0);
        }
    }
}
