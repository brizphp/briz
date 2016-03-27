<?php
namespace Briz\View;

use Psr\Http\Message\ResponseInterface;

/**
 * Basic components for implementing a view.
 */
abstract class BaseView
{

    /**
     * @var string
     */
    protected $file = null;

    /**
     * @var Psr\Http\Message\ResponseInterface 
     */
    protected $response = null;

    /**
     * @var object 
     */
    protected $inherit = null;

    /**
     * @var string 
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $viewDir;

    /**
     * @var string 
     */
    protected $viewPath;

    /**
     * Set File name of the view
     * 
     * @param string $rootDir root directory
     * @param string $viewDir where is the directoy for view in root directory
     * @param string $name name of the current route
     * @param string $file filename without extension. extension should be managed by view
     */
    public function setFile($rootDir, $viewDir, $name, $file)
    {
        $this->viewDir = $viewDir;
        $this->rootDir = $rootDir;
        $this->file = $file;
        $this->viewPath = $rootDir . '/' . $viewDir . '/' . $name . '/' . $file;
    }

    /**
     * Set Response object tobe used by the view.
     * 
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * set route inheritance object
     * @param object $inherit
     */
    public function setInherit($inherit)
    {
        $this->inherit = $inherit;
    }

    /**
     * write output of the view to response with given parameters.
     * 
     * @param string $name name of the route.
     * @param array $params parameters to be passed to view
     */
    abstract public function render($name, $params);
}
