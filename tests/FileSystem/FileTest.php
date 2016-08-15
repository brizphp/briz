<?php

/*
 * 
 * 
 */
namespace Briz\tests\FileSystem;

/**
 * Description of FileTest
 *
 * @author haseeb
 */
class FileTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $name = './file_juyutio.mxt';
        $file = new \Briz\FileSystem\File();
        $file->write($name, "hello");
        $this->assertTrue($file->exists($name));
        $out = $file->read($name);
        $this->assertEquals('hello', $out);
        $file->delete($name);
        $this->assertFalse($file->exists($name));
    }
}
