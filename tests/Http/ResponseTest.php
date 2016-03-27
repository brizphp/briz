<?php

/*
 * 
 * 
 */

/**
 * Description of ResponseTest
 *
 * @author haseeb
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{

    public function generateResponse()
    {
        return new \Briz\Http\Response(208);
    }

    public $response;

    public function setUp()
    {
        $this->response = $this->generateResponse();
    }

    public function testGetReasonPhrase()
    {
        $reason = $this->response->getReasonPhrase();
        $this->assertequals('Already Reported', $reason);
    }

    public function testGetStatusCode()
    {
        $code = $this->response->getStatusCode();
        $this->assertEquals(208, $code);
    }

    public function testSetContent()
    {
        $this->response->setContent('hello');
        $this->response->setContent(' ');
        $this->response->setContent('world');
        $this->assertEquals('hello world', $this->response->getBody());
    }

    public function testSetHeader()
    {
        $this->response->setHeader('x-requested', 'briz', false);
        $this->response->setHeader('x-requested', 'framework', false);
        $this->assertEquals(['briz', 'framework'], $this->response->getHeader('x-requested'));
    }

    public function testWithStatus()
    {
        $response = $this->response->withStatus(200, 'oooookkkk');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('oooookkkk', $response->getReasonPhrase());
        $this->setExpectedException('InvalidArgumentException');
        $response = $this->response->withStatus(2000);
    }

}
