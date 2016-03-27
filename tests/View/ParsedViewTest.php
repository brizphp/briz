<?php
/*
 * 
 * 
 */
namespace Briz\Tests\View;

/**
 * Description of ParsedViewTest
 *
 * @author haseeb
 */
class ParsedViewTest extends \PHPUnit_Framework_TestCase
{
    private static $folder;
    public static function setUpBeforeClass()
    {
        $string = '<?php echo $name;';
        self::$folder = './tmp_View';
        if(is_dir(self::$folder)){
            self::$folder = self::$folder.'ygduasguasgfyagsf';
        }
        mkdir(self::$folder);
        mkdir(self::$folder.'/test');
        file_put_contents(self::$folder.'/test/test.view.php', $string);
    }
    
    public static function tearDownAfterClass()
    {
        unlink(self::$folder.'/test/test.view.php');
        rmdir(self::$folder.'/test');
        rmdir(self::$folder);
    }

    public function testRender()
    {
        $response = new \Briz\Http\Response();
        $render = new Render();
        $render->setResponse($response);
        $render->file = 'test';
        $render->viewPath = self::$folder.'/test/test';
        $inherit = new \stdClass();
        $inherit->test = new \stdClass();
        $inherit->test->parent = null;
        $render->setInherit($inherit);
        $response = $render->render('test', ['name'=>'b']);
        $this->assertInstanceOf('Briz\Http\Response', $response);
        $this->assertEquals('b', (string)$response->getBody());
    }
}

class Render extends \Briz\View\ParsedView{
    public $viewPath;
    public $file;
}
