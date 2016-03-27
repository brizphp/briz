<?php
/*
 * 
 * 
 */
namespace Briz\Tests\Base;

/**
 * Description of ContainerTest
 *
 * @author haseeb
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainer()
    {
        $container = new \Briz\Base\Container();
        $container['name'] = 'value';
        $this->assertFalse($container->has('not'));
        $this->assertTrue($container->has('name'));
        $this->assertEquals('value',$container->get('name'));
    }
    
    public function testException()
    {
        $container = new \Briz\Base\Container();
        $this->setExpectedException('InvalidArgumentException');
        $c = $container->get('not');
    }
}
