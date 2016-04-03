<?php
namespace Briz\Concrete;

/**
 * connect a briz to controller class.
 * this code uses php reflection to add properties
 * see ControllerResolver
 */
abstract class BController
{

    /**
     *
     * @var Interop\Container\ContainerInterface 
     */
    protected $container;
    
    /**
     *
     * @var Psr\Http\Message\ServerRequestInterface
     */
    protected $request;
    
    /**
     *
     * @var Psr\Http\Response
     */
    protected $response;

    /**
     * call the briz renderer function with all passed arguments.
     *
     * @return Psr\Http\Message\ResponseInterface
     */
    protected function renderer()
    {
        $args = func_get_args();
        return call_user_func_array(array($this->briz, 'renderer'), $args);
    }

    /**
     * redirect to a given url.
     * 
     * @param string $url
     * @param int $code
     * @return Psr\Http\Message\ResponseInterface with redirect
     */
    protected function redirect($url, $code = 302)
    {
        return $this->briz->redirect($url, $code);
    }

    /**
     * Show 404 not found error page
     * 
     * shows the error page as defined in ErrorController.php
     * 
     * @return Psr\Http\Message\ResponseInterface with 404 Page
     */
    protected function show404()
    {
        return $this->briz->show404();
    }

}
