<?php
/*
 * 
 * 
 */
namespace Briz\tests\View;

/**
 * Description of JsonViewTest
 *
 * @author haseeb
 */
class JsonViewTest extends \PHPUnit_Framework_TestCase
{

    public function testRenderer()
    {
        $render = new \Briz\View\JsonView();
        $response = new \Briz\Http\Response();
        $render->setResponse($response);
        $response = $render->render('', ['hello' => 'a']);
        $this->assertEquals(['hello' => 'a'], json_decode((string) $response->getBody(), true));
    }
}
