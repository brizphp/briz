<?php

/*
 * 
 * 
 */
namespace Briz\tests\Http;

/**
 * Description of RequestTest
 *
 * @author haseeb
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

    public $request;

    protected function getRequest($method = 'GET', $input = '')
    {
        $uri = new \Briz\Http\Uri("http", "example.com", '/bridge_framework/www/profile', 'a=fg&b=k', '', 80);
        $headers = new \Briz\Http\Collections\Headers(['X_foo' => 'v']);
        $stream = fopen('php://temp', 'w+');
        $body = new \Briz\Http\Stream($stream);
        $body->write($input);
        $server = new \Briz\Http\Collections\ServerParams();
        $server->set('SERVER_PROTOCOL', 'HTTP/1.1');
        $server->set('REQUEST_METHOD', 'GET');
        $cookie = ['foo' => 'bar'];
        $uploaded = [new \Briz\Http\UploadedFile('foo.txt'), new \Briz\Http\UploadedFile('bar.txt')];
        $version = '1.1';
        $attr = ['foo' => 'bar'];
        return new \Briz\Http\Request($method, $uri, $headers, $body, $server, $cookie, $uploaded, $version, $attr);
    }

    public function setUp()
    {
        $this->request = $this->getRequest();
    }

    public function testGetAttributes()
    {
        $attr = $this->request->getAttributes();
        $expected = ['foo' => 'bar'];
        $this->assertEquals($expected, $attr);
    }

    public function testGetAttribute()
    {
        $attr = $this->request->getAttribute('foo');
        $expected = 'bar';
        $this->assertEquals($expected, $attr);
    }

    public function testGetCookieParams()
    {
        $cookies = $this->request->getCookieParams();
        $this->assertEquals(['foo' => 'bar'], $cookies);
    }

    public function testGetMethod()
    {
        $this->assertEquals('GET', $this->request->getMethod());
    }

    public function testGetparsedBody()
    {
        $request = $this->getRequest('POST', json_encode(['a' => 'b']));
        $request = $request->withHeader('content_type', 'application/json');
        $expected = ['a' => 'b'];
        $this->assertEquals($expected, $request->getParsedBody());
    }

    public function testGetQueryParams()
    {
        $this->assertEquals(['a' => 'fg', 'b' => 'k'], $this->request->getQueryParams());
    }

    public function testGetRequestTarget()
    {
        $this->assertequals('/bridge_framework/www/profile?a=fg&b=k', $this->request->getRequestTarget());
    }

    public function testGetServerParams()
    {
        $expected = [
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_METHOD' => 'GET'
        ];

        $this->assertEquals($expected, $this->request->getServerParams());
    }

    public function testGetUploadedFile()
    {
        $files = $this->request->getUploadedFiles();
        foreach ($files as $file) {
            $this->assertInstanceOf('\Briz\Http\UploadedFile', $file);
        }
    }

    public function testGetUri()
    {
        $uri = $this->request->getUri();
        $expected = "http://example.com/bridge_framework/www/profile?a=fg&b=k";
        $this->assertEquals($expected, (string) $uri);
    }

    public function testRegisterParser()
    {
        $callable = function () {
            return ["helloworld"];
        };
        $request = $this->getRequest('POST', json_encode(['a' => 'b']));
        $request = $request->withHeader('content_type', 'application/json');
        $request->registerParser('application/json', $callable);
        $this->assertEquals(['helloworld'], $request->getParsedBody());
    }

    public function testWithAttribute()
    {
        $request = $this->request->withAttribute('name', 'value');
        $actual = $request->getAttribute('name');
        $this->assertEquals('value', $actual);
    }

    public function testWithCookieParams()
    {
        $cookies = ['cookie1' => 'value1', 'cookie2' => 'value2'];
        $request = $this->request->withCookieParams($cookies);
        $this->assertEquals($cookies, $request->getCookieParams());
    }

    public function testWithMethod()
    {
        $method = "post";
        $method2 = "unexpected";
        $request = $this->request->withMethod($method);
        $this->assertEquals('POST', $request->getMethod());
        $this->setExpectedException('\InvalidArgumentException');
        $request = $this->request->withMethod($method2);
    }

    public function testWithParsedBody()
    {
        $request = $this->request->withParsedBody(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $request->getParsedBody());
        $this->setExpectedException('\InvalidArgumentException');
        $request = $this->request->withParsedBody('foo = bar');
    }

    public function withQueryParms()
    {
        $request = $this->request->withQueryParams(['array' => 'array']);
        $this->assertEquals(['array' => 'array'], $request->getQueryParams());
    }

    public function testWithRequestTarget()
    {
        $request = $this->request->withRequestTarget('/briz/www/in');
        $this->assertEquals('/briz/www/in', $request->getRequestTarget());
    }

    public function testWithUploadedFiles()
    {
        $request = $this->request->withUploadedFiles(['newfile.txt']);
        $this->assertEquals(['newfile.txt'], $request->getUploadedFiles());
    }

    public function testWithUri()
    {
        $uri = new \Briz\Http\Uri("https", "example.com", '/briz/www/admin', 'add=new', '', 8021);
        $request = $this->request->withUri($uri);
        $this->assertEquals('https://example.com:8021/briz/www/admin?add=new', (string) $request->getUri());
    }

    public function testWithoutArguments()
    {
        $request = $this->request->withAttribute('new', 'value');
        $request = $request->withoutAttribute('foo');
        $this->assertEquals(['new' => 'value'], $request->getAttributes());
    }
}
