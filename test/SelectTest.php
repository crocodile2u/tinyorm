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

use tinyorm\Select;

class SelectTest extends BaseTestCase
{
    const ROWCOUNT = 10;
    const DENOMINATOR = 3;
    const ZEROS = 4;
    const ONES = 3;
    const TWOS = 3;
    const THREES = 3;
    protected function setUp()
    {
        parent::setUp();
        for ($i = 0; $i < self::ROWCOUNT; $i++) {
            $c_int = $i % self::DENOMINATOR;
            $c_varchar = "varchar " . $c_int;
            $c_unique = "unique " . $i;
            $this->connection->exec("INSERT INTO test (c_varchar, c_int, c_unique)
              VALUES ('$c_varchar', $c_int, '$c_unique')");
            $this->connection->exec("INSERT INTO test2 (c_varchar, c_int, c_unique)
              VALUES ('$c_varchar', $c_int, '$c_unique')");
        }
    }

    function testBasicSelect()
    {
        $rows = $this->createSelect("test")->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(self::ROWCOUNT, count($rows));
    }

    function testBasicCount()
    {
        $rowCount = $this->createSelect("test")->count();
        $this->assertEquals(self::ROWCOUNT, $rowCount);
    }

    function testWhereCondition()
    {
        $select = $this->createSelect("test")->where("c_int = ?", 0);
        $this->assertEquals(self::ZEROS, $select->count());
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(self::ZEROS, count($rows));
    }

    function testLimit()
    {
        $select = $this->createSelect("test")->limit(1);
        $this->assertEquals(self::ROWCOUNT, $select->count());
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(1, count($rows));
    }

    function testOrderBy()
    {
        $select = $this->createSelect("test")->orderBy("c_unique");
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        for ($i = 0; $i < self::ROWCOUNT; $i++) {
            $this->assertEquals("unique $i", $rows[$i]["c_unique"]);
        }
    }

    function testOffset()
    {
        $offset = 5;
        $select = $this->createSelect("test")->orderBy("c_unique")->limit(10)->offset($offset);
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        for ($i = 0; $i < self::ROWCOUNT - $offset; $i++) {
            $val = "unique " . ($i + $offset);
            $this->assertEquals($val, $rows[$i]["c_unique"]);
        }
    }

    function testGroupBy()
    {
        $select = $this->createSelect("test")->groupBy("c_int");
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(self::DENOMINATOR, count($rows));
    }

    function testCountWithExpression()
    {
        $select = $this->createSelect("test")->groupBy("c_int");
        $this->assertEquals(self::DENOMINATOR, $select->count("DISTINCT c_int"));
    }

    function testHaving()
    {
        $select = $this->createSelect("test")->groupBy("c_int")->having("c_int = 0");
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(1, count($rows));
    }

    function testJoin()
    {
        $select = $this->createSelect("test")->join("JOIN test2 USING (c_unique)");
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(self::ROWCOUNT, count($rows));
    }

    function testInClause()
    {
        $select = $this->createSelect("test")->where("c_int IN (?)", [0, 1]);
        $expectedRowCount = self::ZEROS + self::ONES;
        $this->assertEquals($expectedRowCount, $select->count());
    }

    function testGroupByWithParameters()
    {
        $select = $this->createSelect("test")->groupBy("c_int = ?", 0);
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(2, count($rows));
    }

    function testHavingWithParameters()
    {
        $select = $this->createSelect("test")->groupBy("c_int")->having("c_int = ?", 0);
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(1, count($rows));
    }

    function testJoinWithParameters()
    {
        $select = $this->createSelect("test")->join("JOIN test2 ON (test.id = test2.id AND test2.c_int = ?)", "", 0);
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(self::ZEROS, count($rows));
    }

    function testColsWithParameters()
    {
        $addOn = "ADDON";
        $select = (new Select("test", "test.*, ? AS add_on", $addOn))->setConnection($this->connection);
        $rows = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(self::ROWCOUNT, count($rows));
        $this->assertEquals($addOn, $rows[0]["add_on"]);
    }

    function testQueryId()
    {
        $select = $this->createSelect("test")->setId("TEST_ID");
        $str = $select->__toString();
        $this->assertTrue(substr_count($str, "TEST_ID") == 1);
        $stmt = $select->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(self::ROWCOUNT, count($rows));

        $stmt = $select->getCountStatement();
        $sql = $stmt->queryString;
        $this->assertTrue(substr_count($sql, "TEST_ID") == 1);
        $this->assertEquals(self::ROWCOUNT, $stmt->fetchColumn());
    }

    /**
     * @param $table
     * @return Select
     */
    protected function createSelect($table)
    {
        return (new Select($table))->setConnection($this->connection);
    }
}
