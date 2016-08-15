<?php

/*
 * 
 * 
 */
namespace Briz\Tests\Beam;

/**
 * Description of HeaderTest
 *
 * @author haseeb
 */
class HeaderIdentityTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    protected $identity;
    public function setUp()
    {
        $this->container = new \Briz\Base\Container();
        $this->identity = new HeaderIdentity();
        $request = $this->getMockBuilder('Briz\Http\Request')->disableOriginalConstructor()->getMock();
        $request->method('getHeaders')->willReturn(['x_head' => ['none']]);
        $this->container['request'] = $request;
        $this->identity->container = $this->container;
    }
    
    public function testdentify()
    {
        $this->identity->addIdentity('test', 'x-head', 'none');
        $this->identity->addIdentity('test', 'x-head', 'one');
        $this->assertFalse($this->identity->checkByComponent('test'));
        $this->identity->removeIdentity('test', 'x-head');
        $this->assertTrue($this->identity->checkByComponent('test'));
        $this->identity->addIdentity('test', 'x-head', 'none');
        $this->identity->addIdentity('another', 'x-head', 'one');
        $this->assertTrue($this->identity->checkByComponent('test'));
        $this->assertFalse($this->identity->checkByKey('another', 'x-head'));
        $this->assertTrue($this->identity->checkByKey('test', 'x-head'));
        $this->identity->removeComponent('another');
        $this->assertTrue($this->identity->checkByKey('another', 'x-head'));
        $this->assertTrue($this->identity->checkByComponent('another'));
    }
}

class HeaderIdentity extends \Briz\Beam\HeaderIdentity
{
    public $container;
}
