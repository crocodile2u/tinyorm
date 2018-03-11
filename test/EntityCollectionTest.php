<?php

class EntityCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testArrayAccess()
    {
        $entity1 = $this->createEntityMock();
        $entity2 = $this->createEntityMock();
        $entity3 = $this->createEntityMock();
        $collection = new \tinyorm\EntityCollection([$entity1]);
        $collection->append($entity2);
        $collection["key"] = $entity3;

        $this->assertCount(3, $collection);
        $this->assertEquals($entity1, $collection[0]);
        $this->assertEquals($entity2, $collection[1]);
        $this->assertEquals($entity3, $collection["key"]);
    }

    /**
     * @expectedException \TypeError
     */
    public function testTypeErrorInAppend()
    {
        $collection = new \tinyorm\EntityCollection();
        $collection->append("string");
    }

    /**
     * @expectedException \TypeError
     */
    public function testTypeErrorInOffsetSet()
    {
        $collection = new \tinyorm\EntityCollection();
        $collection["key"] = "string";
    }

    public function testSave()
    {
        $entity1 = $this->createEntityMock();
        $entity1->expects($this->once())
            ->method("save")
            ->willReturn(true);
        $entity2 = $this->createEntityMock();
        $entity2->expects($this->once())
            ->method("save")
            ->willReturn(true);

        $collection = new \tinyorm\EntityCollection([$entity1, $entity2]);
        $this->assertTrue($collection->save());

    }

    public function testDelete()
    {
        $entity1 = $this->createEntityMock();
        $entity1->expects($this->once())
            ->method("delete")
            ->willReturn(true);
        $entity2 = $this->createEntityMock();
        $entity2->expects($this->once())
            ->method("delete")
            ->willReturn(true);

        $collection = new \tinyorm\EntityCollection([$entity1, $entity2]);
        $this->assertTrue($collection->delete());
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|\tinyorm\Entity
     */
    protected function createEntityMock()
    {
        return $this->getMockBuilder(\tinyorm\Entity::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}