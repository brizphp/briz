<?php
/*
 * 
 * 
 */
namespace Briz\Tests\Route;

/**
 * Description of RouterTest
 *
 * @author haseeb
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{

    protected $router;
    protected $container;

    public function setUp()
    {
        $this->container = new \Briz\Base\Container();
        $this->container['route_collector'] = new \Briz\Base\Collection();
        $this->container['routes'] = new \Briz\Base\Collection();
        $this->router = new \Briz\Route\Router('test');
        $rf = new \ReflectionClass($this->router);
        $pl = $rf->getMethod('_setup');
        $c = $rf->getProperty('container');
        $c->setAccessible(true);
        $c->setValue($this->router, $this->container);
        $pl->setAccessible(true);
        $pl->invoke($this->router,false, []);
        $this->container['routes']->set('test', $this->router);
    }
    
    public function testGet()
    {
        $this->router->get('/hello', 'callback');
        $data = $this->container->get('route_collector')->get('_Router_test')->getData();
        $this->assertArrayHasKey('/hello', $data[0]['GET']);
    }
    
    public function testPost()
    {
        $this->router->post('/hello', 'callback');
        $data = $this->container->get('route_collector')->get('_Router_test')->getData();
        $this->assertArrayHasKey('/hello', $data[0]['POST']);
    }
    
    public function testPut()
    {
        $this->router->put('/hello', 'callback');
        $data = $this->container->get('route_collector')->get('_Router_test')->getData();
        $this->assertArrayHasKey('/hello', $data[0]['PUT']);
    }
    
    public function testPatch()
    {
        $this->router->patch('/hello', 'callback');
        $data = $this->container->get('route_collector')->get('_Router_test')->getData();
        $this->assertArrayHasKey('/hello', $data[0]['PATCH']);
    }
    
    public function testDelete()
    {
        $this->router->delete('/hello', 'callback');
        $data = $this->container->get('route_collector')->get('_Router_test')->getData();
        $this->assertArrayHasKey('/hello', $data[0]['DELETE']);
    }
    public function testOptions()
    {
        $this->router->options('/hello', 'callback');
        $data = $this->container->get('route_collector')->get('_Router_test')->getData();
        $this->assertArrayHasKey('/hello', $data[0]['OPTIONS']);
    }

        public function testAny()
    {
        $this->router->any('/hello', 'callback');
        $data = $this->container->get('route_collector')->get('_Router_test')->getData();
        $this->assertArrayHasKey('/hello', $data[0]['PUT']);
        $this->assertArrayHasKey('/hello', $data[0]['PATCH']);
        $this->assertArrayHasKey('/hello', $data[0]['DELETE']);
        $this->assertArrayHasKey('/hello', $data[0]['PUT']);
        $this->assertArrayHasKey('/hello', $data[0]['POST']);
        $this->assertArrayHasKey('/hello', $data[0]['GET']);
        $this->assertArrayHasKey('/hello', $data[0]['OPTIONS']);
    }
    
    public function testSet()
    {
        $this->router->set(['POST'], '/h', 'callback');
        $data = $this->container->get('route_collector')->get(\Briz\Route\Router::PREFIX.'test')->getData();
        $this->assertEquals('callback', $data[0]['POST']['/h']);
    }
    
    public function testGetName()
    {
        $name = $this->router->getName();
        $this->assertEquals(\Briz\Route\Router::PREFIX.'test', $name);
    }
    
    public function testGetParent()
    {
        $parent = $this->router->getParent();
        $this->assertNull($parent);
    }

    public function testGetRealName()
    {
        $name = $this->router->getRealName();
        $this->assertEquals('test', $name);
    }
        
    public function testSetRenderer()
    {
        $this->router->setRenderer('renderer');
        $this->assertEquals('renderer', $this->router->getRenderer());
    }
    
    public function testGetBridge()
    {
        $this->container['request'] = '';
        $this->container['response'] = '';
        $briz = $this->router->getBridge();
        $this->assertInstanceOf('Briz\Route\BridgeRoutes', $briz);
    }
    
    protected function setupIdentify($t1 = true,$t2=true)
    {
        $mock = $this->getMockBuilder('Briz\Beam\HeaderIdentity');
        $identity = $mock->getMock();
        $identity->method('checkByComponent')->willReturn($t1);
        $this->container['id'] = new \Briz\Base\Collection();
        $this->container->get('id')->set('header',$identity);
        $identity2 = $this->getMockBuilder('Briz\Beam\HeaderIdentity')->getMock();
        $identity2->method('checkByComponent')->willReturn($t2);
        $this->container->get('id')->set('another',$identity2);
    }
    
    public function testIdentifyNokey()
    {
        $this->setupIdentify();
        $this->setExpectedException('BadMethodCallException');
        $this->router->identify();
    }
    
    public function testIdentifyLessArgs()
    {
        $this->setupIdentify();
        $this->setExpectedException('BadMethodCallException');
        $this->router->identify(2);
    }
    
     public function testIdentifyInvalid()
    {
        $this->setupIdentify();
        $this->setExpectedException('InvalidArgumentException');
        $this->router->identify('i','j');
    }
    
    public function testIdentify()
    {
        $this->setupIdentify(true,true);
        $this->router->identify('header','he','invalid');
        $this->router->identify('another','he','invalid');
        $array = $this->router->getIdentity();
        $this->assertArrayHasKey('header', $array);
        $this->assertTrue($this->router->checkIdentity());
    }
    
    public function testGetIdentity()
    {
        $this->setupIdentify(true,false);
        $this->router->identify('header','he','invalid');
        $this->router->identify('header','help','invalid');
        $this->router->identify('another','he','invalid');
        $this->router->identify('another','j','k');
        $this->assertArrayHasKey('header', $this->router->getIdentity());
        $this->assertArrayHasKey('another', $this->router->getIdentity());
        $this->assertCount(2, $this->router->getIdentity());
    }

    public function testheckIdentityFalse()
    {
        $this->setupIdentify(true,false);
        $this->router->identify('header','he','invalid');
        $this->router->identify('another','he','invalid');
        $this->assertFalse($this->router->checkIdentity());
    }
    
    public function testheckIdentityFalseTogle()
    {
        $this->setupIdentify(false,true);
        $this->router->identify('header','he','invalid');
        $this->router->identify('another','he','invalid');
        $this->assertFalse($this->router->checkIdentity());
    }
    
    public function testheckIdentityFalseAll()
    {
        $this->setupIdentify(false,false);
        $this->router->identify('header','he','invalid');
        $this->router->identify('another','he','invalid');
        $this->assertFalse($this->router->checkIdentity());
    }
    
    public function testRun()
    {
        $this->router->get('\hello', 'str');
        $result = $this->router->run('GET', '\hello');
        $this->assertEquals('str', $result[1]);
    }
    
}
