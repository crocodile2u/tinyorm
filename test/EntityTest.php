<?php

namespace tinyorm\test;

use tinyorm\Entity;
use tinyorm\persistence\DbDriver;

class EntityTest extends BaseTestCase {

    /**
     * @var DbDriver
     */
    private $persistenceDriver;

    function testFind()
    {
        $id = $this->assertEntitySaved();
        $entity = TestEntity::find($id);
        $this->assertInstanceOf(TestEntity::class, $entity);
        $this->assertEquals($id, $entity->getPK());
        $this->assertEquals("UNIQUE", $entity->c_unique);
    }

    function testSave()
    {
        $entity = new TestEntity([
            "c_unique" => "UNIQUE"
        ]);
        $entity->save();
        $this->assertGreaterThan(0, $entity->getPK());
    }

    function testDelete()
    {
        $entity = new TestEntity([
            "c_unique" => "UNIQUE"
        ]);
        $this->persistenceDriver->save($entity, $rowCount);
        $this->assertTrue($entity->delete());
        $this->assertNull(TestEntity::find($entity->getPK()));
    }

    /**
     * @return int new entity ID
     */
    function assertEntitySaved()
    {
        $entity = new TestEntity([
            "c_unique" => "UNIQUE"
        ]);
        $this->persistenceDriver->save($entity, $rowCount);
        $this->assertEquals(1, $rowCount);
        return $entity->getPK();
    }

    function setUp()
    {
        parent::setUp();
        $this->persistenceDriver = new DbDriver($this->connection);
        Entity::setDefaultPersistenceDriver($this->persistenceDriver);
    }

    function tearDown()
    {
        parent::tearDown();
        $this->persistenceDriver = null;
        Entity::unsetDefaultPersistenceDriver();
    }
}