<?php

namespace tinyorm\test;

abstract class PersistenceDriverTest extends BaseTestCase {

    protected function isTestSkipped()
    {
        return false;
    }

    function testFind() {
        $this->skipTestIfNeeded();
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
        $this->skipTestIfNeeded();
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
        $this->skipTestIfNeeded();
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
        $this->skipTestIfNeeded();
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
        $this->skipTestIfNeeded();
        $id = $this->insert("v1", 1, "u1");
        $persistenceDriver = $this->createPersistenceDriver();
        $entity = $persistenceDriver->find($id, new TestEntity());
        $result = $persistenceDriver->delete($entity);
        $this->assertTrue($result);
        $this->assertNull($persistenceDriver->find($id, new TestEntity()));
    }

    protected function insert($varchar, $int, $unique) {
        $this->connection->exec("INSERT INTO test (c_varchar, c_int, c_unique)
              VALUES ('$varchar', $int, '$unique')");
        return $this->connection->lastInsertId();
    }

    /**
     * @return \tinyorm\persistence\Driver
     */
    abstract protected function createPersistenceDriver();

    protected function skipTestIfNeeded()
    {
        if ($this->isTestSkipped()) {
            $this->markTestSkipped("ZHandlersocket extension not found. Checkout https://github.com/crocodile2u/zhandlersocket");
        }
    }
}