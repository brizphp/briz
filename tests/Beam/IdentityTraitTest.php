<?php
/*
 * 
 * 
 */
namespace Briz\Tests\Beam;

/**
 * Description of IdentityTraitTest
 *
 * @author haseeb
 */
class IdentityTraitTest extends \PHPUnit_Framework_TestCase
{

    protected $container;
    protected $identity;
    
    public function setUp()
    {
        $this->identity = new TestCase();
        $this->container = new \Briz\Base\Container();
        $this->container['id'] = new \Briz\Base\Collection();
        $header = $this->getMock('Briz\Beam\HeaderIdentity');
        $header->method('checkByComponent')->willReturn(true);
        $header->method('checkByKey')->willReturn(false);
        $this->container->get('id')->set('header',  $header);
        $header = $this->getMock('Briz\Beam\HeaderIdentity');
        $header->method('checkByComponent')->willReturn(false);
        $this->container->get('id')->set('header2',  $header);
        $this->identity->container = $this->container;
    }
    
    public function testAddIdentity()
    {
        $this->identity->addIdentity('header', 'x-head', 'none');
        $this->identity->addIdentity('header2', 'x-head', 'none');
        $this->assertArrayHasKey('header', $this->identity->g());
        $this->assertArrayHasKey('header2', $this->identity->g());
    }
    
    public function testRemoveIdentity()
    {
        $this->identity->addIdentity('header', 'x-head', 'none');
        $this->identity->addIdentity('header', 'x-header', 'none');
        $this->identity->removeIdentity('header', 'x-head');
        $this->assertArrayHasKey('header', $this->identity->g());
        $this->identity->removeIdentity('header', 'x-header');
        $this->assertArrayNotHasKey('header', $this->identity->g());
    }
    
    public function testRemoveAllIdentitty()
    {
        $this->identity->addIdentity('header', 'x-head', 'none');
        $this->identity->addIdentity('header', 'x-header', 'none');
        $this->identity->addIdentity('header2', 'x-head', 'none');
        $this->identity->removeAllIdentity();
        $this->assertEmpty($this->identity->g());
        $this->assertEmpty($this->identity->x());
    }
    public function testRemoveAllIdentityName()
    {
        $this->identity->addIdentity('header', 'x-head', 'none');
        $this->identity->addIdentity('header', 'x-header', 'none');
        $this->identity->addIdentity('header2', 'x-head', 'none');
        $this->identity->removeAllIdentity('header');
        $this->assertArrayHasKey('header2', $this->identity->g());
        $this->assertArrayNotHasKey('header', $this->identity->g());
    }
    
    public function testIdentifyAll()
    {
        $this->identity->addIdentity('header', 'x-head', 'none');
        $this->identity->addIdentity('header', 'x-header', 'none');
        $this->identity->addIdentity('header2', 'x-head', 'none');
        $this->assertFalse($this->identity->identifyAll());
        $this->identity->removeAllIdentity('header2');
        $this->assertTrue($this->identity->identifyAll());
    }
    
    public function testIdentifyByName()
    {
        $this->identity->addIdentity('header', 'x-head', 'none');
        $this->identity->addIdentity('header', 'x-header', 'none');
        $this->identity->addIdentity('header2', 'x-head', 'none');
        $this->assertFalse($this->identity->identifyByName('header2'));
        $this->assertTrue($this->identity->identifyByName('header'));
        $this->assertFalse($this->identity->identifyByName('header', 'key'));
    }
}

class TestCase
{

    public $container;
    public function g()
    {
        return $this->identities;
    }
    public function x()
    {
        return $this->idt_keyIndex;
    }

    use \Briz\Beam\IdentityTrait;
}
