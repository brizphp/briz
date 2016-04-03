<?php

/*
 * 
 * 
 */
namespace Briz\Tests\Helpers;

/**
 * Description of testHelpers
 *
 * @author haseeb
 */
class HelpersTest extends \PHPUnit_Framework_TestCase
{
    public function testFakeMethodInBody()
    {
        $stream = fopen('php://temp', 'w+');
        $body = new \Briz\Http\Stream($stream);
        $content = json_encode(['X-Http-Method-Override' => 'put']);
        $body->write($content);
        $container = new \Briz\Base\Container();
        $uri = new \Briz\Http\Uri();
        $headers = new \Briz\Http\Collections\Headers(['content-type'=>'application/json']);
        $server = new \Briz\Http\Collections\ServerParams();
        $request = new \Briz\Http\Request('GET',$uri,$headers,$body,$server,[]);
        $container['request'] = $request;
        \Briz\Helpers\LoadHelpers::fakeMethod($request, 'X-Http-Method-Override',$container);
        $request = $container->get('request');
        $this->assertEquals('PUT', $request->getMethod());
    }
    
    public function testFakeMethodInBodyNonMethod()
    {
        $container = new \Briz\Base\Container();
        $stream = fopen('php://temp', 'w+');
        $body = new \Briz\Http\Stream($stream);
        $content = json_encode(['X-Http-Method-Override' => 'lalalalala']);
        $body->write($content);
        $uri = new \Briz\Http\Uri();
        $headers = new \Briz\Http\Collections\Headers(['content-type'=>'application/json']);
        $server = new \Briz\Http\Collections\ServerParams();
        $request = new \Briz\Http\Request('GET',$uri,$headers,$body,$server,[]);
        $container['request'] = $request;
        \Briz\Helpers\LoadHelpers::fakeMethod($request, 'X-Http-Method-Override',$container);
        $request = $container->get('request');
        $this->assertEquals('GET', $request->getMethod());
    }
    
    public function testFakeMethodInHeader()
    {
        $container = new \Briz\Base\Container();
        $stream = fopen('php://temp', 'w+');
        $body = new \Briz\Http\Stream($stream);
        $uri = new \Briz\Http\Uri();
        $headers = new \Briz\Http\Collections\Headers();
        $headers->set('X-Http-Method-Override', 'put');
        $server = new \Briz\Http\Collections\ServerParams();
        $request = new \Briz\Http\Request('GET',$uri,$headers,$body,$server,[]);
        $container['request'] = $request;
        \Briz\Helpers\LoadHelpers::fakeMethod($request, 'X-Http-Method-Override',$container);
        $request = $container->get('request');
        $this->assertEquals('PUT', $request->getMethod());
    }
    
    public function testFakeMethodInHeaderNonMethod()
    {
        $container = new \Briz\Base\Container();
        $stream = fopen('php://temp', 'w+');
        $body = new \Briz\Http\Stream($stream);
        $uri = new \Briz\Http\Uri();
        $headers = new \Briz\Http\Collections\Headers();
        $headers->set('X-Http-Method-Override', 'lalalalala');
        $server = new \Briz\Http\Collections\ServerParams();
        $request = new \Briz\Http\Request('GET',$uri,$headers,$body,$server,[]);
        $container['request'] = $request;
        \Briz\Helpers\LoadHelpers::fakeMethod($request, 'X-Http-Method-Override',$container);
        $request = $container->get('request');
        $this->assertEquals('GET', $request->getMethod());
    }
}
