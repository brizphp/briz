<?php

/*
 * 
 * 
 */

namespace Briz\Tests\Http;

/**
 * Description of UriTest
 *
 * @author haseeb
 */
class UriTest extends \PHPUnit_Framework_TestCase{
    
    protected $uri;
    
    public function setUp() {
        $this->uri = new \Briz\Http\Uri(
                "http",
                'example.com',
                'briz/tests',
                'a=b&b=c',
                'title',
                8080,
                'haseeb', 
                'nosecret'
                );
    }
    public function testGetAuthority()
    {
        $this->assertEquals('haseeb:nosecret@example.com:8080', $this->uri->getAuthority());
    }
    
    public function testGetFragment()
    {
        $this->assertEquals('title', $this->uri->getFragment());
    }
    public function testGetHost()
    {
        $this->assertEquals('example.com', $this->uri->getHost());
    }
    
    public function testGetPath()
    {
        $this->assertEquals('briz/tests',  $this->uri->getPath());
    }
    
    public function testGetPort()
    {
        $this->assertEquals('8080', $this->uri->getPort());
    }
    public function testGetQuery()
    {
        $this->assertEquals('a=b&b=c', $this->uri->getQuery());
    }
    
    public function testgetScheme()
    {
        $this->assertequals('http',  $this->uri->getScheme());
    }
    
    public function testgetUserInfo()
    {
        $this->assertEquals('haseeb:nosecret', $this->uri->getUserInfo());
    }
    public function testWithFragment()
    {
        $uri = $this->uri->withFragment('paragraph');
        $this->assertEquals('paragraph', $uri->getFragment());
    }
    
    public function testwithHost()
    {
        $uri = $this->uri->withHost('github.io');
        $this->assertEquals('github.io',  $uri->getHost());
    }
    
    public function testwithPath()
    {
        $uri = $this->uri->withPath('/briz/example');
        $this->assertEquals('/briz/example', $uri->getPath());
    }
    
    public function testWithPort()
    {
        $uri = $this->uri->withPort(80);
        $this->assertEquals(80,  $uri->getPort());
    }
    public function testWithQuery()
    {
        $uri = $this->uri->withQuery('page=1');
        $this->assertEquals('page=1', $uri->getQuery());
    }
    
    public function testWithScheme()
    {
        $uri = $this->uri->withScheme('https');
        $this->assertEquals('https', $uri->getScheme());
    }
    
    public function testWithUserInfo()
    {
        $uri = $this->uri->withUserInfo('notme', 'secret');
        $this->assertEquals('notme:secret', $uri->getUserInfo());
    }
}
