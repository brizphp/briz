<?php
/*
 * 
 * 
 */
namespace Briz\tests\Route;

/**
 * Description of RouteTest
 *
 * @author haseeb
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{

    protected $route;
    public function setUp()
    {
        $this->route = new \Briz\Route\Route('test');
    }

    public function testDispach()
    {
        $this->route->addRoute('get', '/a', false);
        $this->route->addRoute('get', '/b', true);
        $this->route->addRoute('post', '/b', false);
        $route = $this->route->dispatch('rend', 'get', '/b');
        $this->assertTrue($route[1]);
        $route = $this->route->dispatch('rend', 'post', '/b');
        $this->assertFalse($route[1]);
    }
    
    public function testRouteConflict()
    {
        $this->setExpectedException('\FastRoute\BadRouteException');
        $this->route->addRoute('get', '/a', 'and');
        $this->route->addRoute('get', '/a', 'foo');
    }
}
