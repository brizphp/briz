<?php
namespace Briz\Tests\Http;
/**
 * Description of StreamTest
 *
 * @author haseeb
 */
class StreamTest extends \PHPUnit_Framework_TestCase{

    public function getStream() 
    {
        $stream = fopen("php://temp", "r+");
        $stream = new Stream($stream);
        return $stream;
    }
    
    public function testClose()
    {
        $stream = $this->getStream();
        $stream->close();
        $this->assertFalse(is_resource($stream->stream));
    }
    
    public function testDetach()
    {
        $stream = $this->getStream();
        $stream->detach();
        $this->assertNull($stream->stream);
    }
    
    public function testEof()
    {
        $stream = $this->getStream();
        $stream->rewind();
        $this->assertFalse($stream->eof());
        while (!feof($stream->stream)){
            fread($stream->stream, 4096);
        }
        $this->assertTrue($stream->eof());
    }
    
    public function testGetContents()
    {
        $stream = $this->getStream();
        $stream->write("hello_world");
        $stream->rewind();
        $this->assertEquals('hello_world',  $stream->getContents() );
    }
    
    public function testGetMetaData()
    {
        $stream = $this->getStream();
        $meta = $stream->getMetadata();
        $this->assertEquals('php://temp', $meta['uri']);
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
    }
    
    public function testGetSize()
    {
        $stream = $this->getStream();
        $stream->write("\n\n");
        $this->assertEquals('2', $stream->getSize());
        $stream->close();
    }
    
    public function testIsReadable()
    {
        $stream = fopen("php://temp", 'r');
        $stream = new Stream($stream);
        $this->assertTrue($stream->isReadable());
        $stream->close();
        $stream2 = fopen('php://output', 'w');
        $stream2 = new Stream($stream2);
        $this->assertFalse($stream2->isReadable());
        $stream2->close();
    }
   public function testIsSeekable()
   {
       $stream = $this->getStream();
       $this->assertTrue($stream->isSeekable());
       $stream->close();
       $stream = fopen('php://output', 'w');
       $stream = new Stream($stream);
       $this->assertFalse($stream->isSeekable());
   }
   
   public function testIsWritable()
   {
       $stream = $this->getStream();
       $this->assertTrue($stream->isWritable());
       $stream->close();
       $stream = fopen('php://input', 'r');
       $stream = new Stream($stream);
       $this->assertFalse($stream->isWritable());
   }
   
   public function testReadSeekRewindAndWrite()
   {
       $stream = $this->getStream();
       $stream->write("hello_world");
       $stream->rewind();
       $actual = $stream->read(5);
       $this->assertEquals('hello', $actual);
       $stream->rewind();
       $stream->seek(6);
       $actual = $stream->read(5);
       $this->assertEquals('world', $actual);
   }
   
   public function testTell()
   {
       $stream = $this->getStream();
       $stream->write("hello_world");
       $stream->rewind();
       $stream->seek(6);
       $this->assertEquals(6, $stream->tell());
   }
}

class Stream extends \Briz\Http\Stream{
    public $stream;
}
