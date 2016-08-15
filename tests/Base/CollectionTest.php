<?php

namespace Briz\tests\Base;

use Briz\Base\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Collection 
     */
    private $collection;

    /**
     * @var ReflectionProperty
     */
    private $property;

    public function setUp()
    {
        $this->collection = new Collection();
        $this->property = new \ReflectionProperty($this->collection, 'items');
        $this->property->setAccessible(true);
    }

    public function testConstruct()
    {
        $data = ['foo' => 'bar'];
        $bag = new Collection($data);
        $bagProperty = new \ReflectionProperty($bag, 'items');
        $bagProperty->setAccessible(true);

        $this->assertEquals($data, $bagProperty->getValue($bag));
    }
    
    public function testAll()
    {
        $data = ['foo'=>'bar','data1'=>'data2'];
        $this->property->setValue($this->collection, $data);
        $this->assertEquals($data, $this->collection->all());
    }
    
    public function testClear()
    {
        $data = ['foo'=>'bar','data1'=>'data2'];
        $this->property->setValue($this->collection, $data);
        $this->collection->clear();
        $this->assertEquals([], $this->collection->all());
    }
    
    public function testCount()
    {
        $data = ['foo'=>'bar','data1'=>'data2'];
        $this->property->setValue($this->collection, $data);
        $count = $this->collection->count();
        $this->assertEquals($count, 2);
    }
    
    public function testGet()
    {
        $data = ['foo'=>'bar','data1'=>'data2','user'=>'abc1'];
        $this->property->setValue($this->collection, $data);
        $this->assertEquals($data['data1'], $this->collection->get('data1'));
    }
    
    public function testHas()
    {
        $data = ['foo'=>'bar','data1'=>'data2','user'=>'abc1'];
        $this->property->setValue($this->collection, $data);
        $this->assertFalse($this->collection->has('none'));
        $this->assertTrue($this->collection->has('data1'));
    }
    
    public function testIsempty()
    {
        $data = ['foo'=>'bar'];
        $this->property->setValue($this->collection, $data);
        $this->assertFalse($this->collection->isempty());
        $data = [];
        $this->property->setValue($this->collection, $data);
        $this->assertTrue($this->collection->isempty());
    }
    
    public function testRemove()
    {
        $data = ['foo'=>'bar','data1'=>'data2','user'=>'abc1'];
        $this->property->setValue($this->collection, $data);
        $this->collection->remove('user');
        $array = $this->property->getValue($this->collection);
        $this->assertArrayNotHasKey('user', $array);
    }
    
    public function testReplace()
    {
        $data1 = ['foo'=>'bar','data1'=>'data2','user'=>'abc1'];
        $data2 = ['foo1'=>'bar','data'=>'data2','user1'=>'abc1'];
        $this->property->setValue($this->collection, $data2);
        $this->collection->replace($data1);
        $this->assertArrayHasKey('data1', $this->property->getValue($this->collection));
        $this->assertArrayNotHasKey('data', $this->property->getValue($this->collection));
    }
    
    public function testSet()
    {
        $this->collection->set('abcd', 'a');
        $this->assertArrayHasKey('abcd', $this->property->getValue($this->collection));
    }
}
