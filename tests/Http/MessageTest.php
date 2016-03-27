<?php

namespace Briz\Tests\Http;

/**
 * Description of MessageTest
 *
 * @author haseeb
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{

    public function testGetBody()
    {
        $body = $this->getBody();
        $message = new Message();
        $message->body = $body;
        $this->assertEquals($message->getBody(), $body);
    }

    public function testGetHeaders()
    {
        $headers = new \Briz\Http\Collections\Headers(
                ['foo' => 'bar',
            'foo1' => 'bar1'
        ]);
        $message = new Message();
        $message->headers = $headers;
        $this->assertEquals($message->getHeaders(), $headers->all());
    }

    public function testgetHeader()
    {
        $headers = new \Briz\Http\Collections\Headers();
        $headers->set('foo', 'bar', false);
        $headers->set('foo', 'bar1', false);
        $expect = ['bar', 'bar1'];
        $message = new Message();
        $message->headers = $headers;
        $this->assertEquals($message->getHeader('foo'), $expect);
    }

    public function testgetHeaderLine()
    {
        $headers = new \Briz\Http\Collections\Headers();
        $headers->set('foo', 'bar', false);
        $headers->set('foo', 'bar1', false);
        $expect = 'bar,bar1';
        $message = new Message();
        $message->headers = $headers;
        $this->assertEquals($message->getHeaderLine('foo'), $expect);
    }

    public function getProtocolVersion()
    {
        $message = new Message();
        $message->protocolVersion = '1.1';
        $this->assertEquals('1.1', $message->getProtocolVersion());
    }

    public function testHasHeader()
    {
        $headers = new \Briz\Http\Collections\Headers(
                ['foo' => 'bar',
            'foo1' => 'bar1'
        ]);
        $message = new Message();
        $message->headers = $headers;
        $this->assertTrue($message->hasHeader('foo'));
        $this->assertFalse($message->hasHeader('bar'));
    }

    public function testWithAddedHeaders()
    {
        $headers = new \Briz\Http\Collections\Headers();
        $headers->set('key', 'value');
        $message = new Message();
        $message->headers = $headers;
        $cloned = $message->withAddedHeader('key', 'foo');
        $this->assertEquals('value,foo', $cloned->getHeaderLine('key'));
    }

    public function testWithBody()
    {
        $body = $this->getBody();
        $message = new Message();
        $cloned = $message->withBody($body);
        $this->assertEquals($body, $cloned->getBody());
    }

    public function testWithHeader()
    {
        $headers = new \Briz\Http\Collections\Headers();
        $headers->set('key', 'value');
        $message = new Message();
        $message->headers = $headers;
        $cloned = $message->withHeader('key', 'value2');
        $this->assertEquals('value2', $cloned->getHeaderLine('key'));
    }

    public function testWithProtocolVersion()
    {
        $message = new Message();
        $message->protocolVersion = '1.0';
        $cloned = $message->withProtocolVersion('1.1');
        $this->assertEquals('1.1', $cloned->getProtocolVersion());
    }

    public function testWithoutHeaders()
    {
        $headers = $headers = new \Briz\Http\Collections\Headers(
                ['foo' => 'bar',
            'foo1' => 'bar1'
        ]);
        $message = new Message();
        $message->headers = $headers;
        $cloned = $message->withoutHeader('foo1');
        $this->assertFalse($message->hasHeader('foo1'));
    }

    protected function getBody()
    {
        return $this->getMockBuilder('Briz\Http\Stream')->disableOriginalConstructor()->getMock();
    }

}

class Message extends \Briz\Http\Message
{

    public $protocolVersion;
    public $headers;
    public $body;

}
