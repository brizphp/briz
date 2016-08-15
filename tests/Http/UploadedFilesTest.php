<?php
/*
 * 
 * 
 */

namespace Briz\tests\Http;

/**
 * Description of UploadedFilesTest
 *
 * @author haseeb
 */
class UploadedFilesTest extends \PHPUnit_Framework_TestCase
{
    
    protected $files;
    public static function setUpBeforeClass()
    {
        $handle = fopen('./filesample.jpeg', 'w');
        fwrite($handle, "some_payload");
        fclose($handle);
    }
    
    public function setUp()
    {
        $_FILES = array(
            'test' => array(
                'name'=>'filesample.jpeg',
                'type'=> 'image/jpeg',
                'size' => 12,
                'tmp_name' => './filesample.jpeg',
                'error' => UPLOAD_ERR_PARTIAL
                )
        );
        $test = $_FILES['test'];
        $this->files = new \Briz\Http\UploadedFile(
                $test['tmp_name'],
                $test['size'],
                $test['error'],
                $test['name'],
                $test['type']
                );
    }

    public static function tearDownAfterClass()
    {
        if (file_exists('./filesample.jpeg')) {
            unlink('./filesample.jpeg');
        }
        if (file_exists('./file1.txt')) {
            unlink('./file1.txt');
        }
        if (file_exists('./file2.txt')) {
            unlink('./file2.txt');
        }
    }
    
    public function testToString()
    {
        $this->assertEquals($_FILES['test']['tmp_name'],  (string)$this->files);
    }
    
    public function testgetClientFilename()
    {
        $expected = $_FILES['test']['name'];
        $this->assertEquals($expected,  $this->files->getClientFilename());
    }
    
    public function testgetClientMediaType()
    {
        $expected = $_FILES['test']['type'];
        $this->assertEquals($expected,  $this->files->getClientMediaType());
    }
    
    public function testgetError()
    {
        $expected = $_FILES['test']['error'];
        $this->assertEquals($expected,  $this->files->getError());
    }
    
    public function testgetErrorMesssage()
    {
        $expected = "The uploaded file was only partially uploaded";
        $this->assertEquals($expected, $this->files->getErrorMessage());
    }
    
    public function testGetSize()
    {
        $expected = $_FILES['test']['size'];
        $this->assertEquals($expected, $this->files->getSize());
    }
    
    public function testGetStream()
    {
        $stream = $this->files->getStream();
        $actual = $stream->getMetadata('uri');
        $this->assertEquals($_FILES['test']['tmp_name'], $actual);
    }
    
    public function testIsImage()
    {
        $this->assertTrue($this->files->isImage());
    }
    public function testMove()
    {
        $target = './file.jpeg';
        $this->files->moveTo($target);
        $this->assertFileExists($target);
        unlink($target);
    }
    public function testGetStreamMoved()
    {
        $file = fopen('./file1.txt', 'w');
        fwrite($file, 'hello');
        $file = new \Briz\Http\UploadedFile('./file1.txt');
        $file->moveTo('./file2.txt');
        $this->setExpectedException('RunTimeException');
        $file->getStream();
    }
}
