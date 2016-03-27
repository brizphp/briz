<?php

/*
 * 
 * 
 */
namespace Briz\Tests\Route;

/**
 * Description of BridgeRoutesTest
 *
 * @author haseeb
 */
class BridgeRoutesTest extends \PHPUnit_Framework_TestCase
{

    protected $container;
    protected $briz;

    public function constructBridge()
    {
        $this->container = new \Briz\Base\Container();
        $this->container['request'] = '';
        $this->container['root_dir'] = __DIR__;
        $this->container['view_engine'] = 'Briz\\View';
        $this->container['default_view_class'] = 'JsonView';
        $this->container['view_dir'] = 'test';
        $inherit = new \stdClass();
        $inherit->test = new \stdClass();
        $inherit->test->child = null;
        $inherit->test->parant = null;
        $this->container['inherit'] = $inherit;
        $this->container['response'] = new \Briz\Http\Response();
        $this->briz = new \Briz\Route\BridgeRoutes('test', $this->container);
    }

    public function setUp()
    {
        $this->constructBridge();
    }

    public function testRedirect()
    {
        $response = $this->briz->redirect("http://www.example.com");
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://www.example.com', $response->getHeaderLine('location'));
    }

    public function testRenderer()
    {
        $values = ['hello' => 'world', 'foo' => 'bar'];
        $response = $this->briz->renderer('y', $values);
        $body = (string) $response->getBody();
        $actual = json_decode($body, true);
        $this->assertEquals($values, $actual);
    }

    //show404 test requires using user namespace so. it is skipped now.
}
