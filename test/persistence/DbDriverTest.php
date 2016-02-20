<?php

namespace tinyorm\test\persistence;

use tinyorm\persistence\DbDriver;
use tinyorm\test\BaseTestCase;
use tinyorm\test\TestEntity;

class DbDriverTest extends BaseTestCase {
    function testFind() {
        $id = $this->insert("v1", 1, "u1");
        /** @var TestEntity $entity */
        $entity = $this->createPersistenceDriver()->find($id, new TestEntity());
        $this->assertInstanceOf(TestEntity::class, $entity);
        $this->assertEquals("v1", $entity->c_varchar);
        $this->assertEquals(1, $entity->c_int);
        $this->assertEquals("u1", $entity->c_unique);
    }

    function testInsert()
    {
        $entity = new TestEntity([
            "c_varchar" => "v1",
            "c_int" => 1,
            "c_unique" => "u1",
        ]);
        $this->assertNull($entity->id);
        $entity = $this->createPersistenceDriver()->insert($entity);
        $this->assertTrue($entity->id > 0);
    }

    function testUpdate()
    {
        $id = $this->insert("v1", 1, "u1");
        $persistenceDriver = $this->createPersistenceDriver();
        $entity = $persistenceDriver->find($id, new TestEntity());
        $entity->c_varchar = "updated";
        $persistenceDriver->update($entity);
        $updatedEntity = $persistenceDriver->find($id, new TestEntity());
        $this->assertEquals("updated", $updatedEntity->c_varchar);
    }

    function testSave()
    {
        $entity = new TestEntity([
            "c_varchar" => "v1",
            "c_int" => 1,
            "c_unique" => "u1",
        ]);
        $this->assertNull($entity->id);
        $persistenceDriver = $this->createPersistenceDriver();
        $entity = $persistenceDriver->save($entity);
        $this->assertTrue($entity->id > 0);

        $entity->c_varchar = "updated";
        $persistenceDriver->save($entity);
        $updatedEntity = $persistenceDriver->find($entity->id, new TestEntity());
        $this->assertEquals("updated", $updatedEntity->c_varchar);
    }

    function testDelete()
    {
        $id = $this->insert("v1", 1, "u1");
        $persistenceDriver = $this->createPersistenceDriver();
        $entity = $persistenceDriver->find($id, new TestEntity());
        $result = $persistenceDriver->delete($entity);
        $this->assertTrue($result);
        $this->assertFalse($persistenceDriver->find($id, new TestEntity()))
    }

    protected function insert($varchar, $int, $unique) {
        $this->connection->exec("INSERT INTO test (c_varchar, c_int, c_unique)
              VALUES ('$varchar', $int, '$unique')");
        return $this->connection->lastInsertId();
    }

    /**
     * @return DbDriver
     */
    protected function createPersistenceDriver() {
        return new DbDriver($this->connection);
    }
}