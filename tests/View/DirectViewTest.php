<?php
/*
 * 
 * 
 */
namespace Briz\tests\View;

/**
 * Description of DirectViewTest
 *
 * @author haseeb
 */
class DirectViewTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $render = new \Briz\View\DirectView();
        $response = new \Briz\Http\Response();
        $render->setResponse($response);
        $render->render('', ['foo ', 'bar'=>'bar']);
        $this->assertEquals('foo bar', (string)$response->getBody());
    }
}
