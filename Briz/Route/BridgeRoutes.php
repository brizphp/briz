<?php
namespace Briz\Route;

use Interop\Container\ContainerInterface;
use Briz\Concrete\ControllerResolver as CR;
use Briz\Beam\IdentityTrait;

/**
 * Bridging and controlling inside a route.
 * 
 * Give Request and response objects for handling
 * and generate response
 */
class BridgeRoutes
{

    use IdentityTrait;

    /**
     * @var Interop\Container\ContainerInterface 
     */
    protected $container;

    /**
     * @var Psr\Http\Message\ServerRequestInterface
     */
    public $request;

    /**
     * @var Psr\Http\Message\ResponseInterface
     */
    public $response;

    /**
     * Which renderer is used.
     * @var callable|string
     */
    protected $rendererCallable;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    public $args;

    /**
     * Create a new BridgeRoutes Instance.
     * 
     * @param string $name
     * @param ContainerInterface $container
     * @param string|callable $rendererCallable
     * @param array $args
     * @throws RunTimeException
     */
    public function __construct(
        $name,
        ContainerInterface $container,
        $rendererCallable = null,
        array $args = []
    ){
        if ($container->has('request') and $container->has('response')) {
            $this->name = $name;
            $this->container = $container;
            $this->request = $container->get('request');
            $this->response = $container->get('response');
            $this->rendererCallable = $rendererCallable;
            $this->args = $args;
        } else {
            throw new \RunTimeException("Request or Response objects not set.");
        }
    }

    /**
     * Use specified renderer to generate response
     * 
     * @param string $file
     * @param array|object $params
     * @return Psr\Http\Message\ResponseInterface
     * @throws RunTimeException
     * @throws InvalidArgumentException
     */
    public function renderer($file, $params = [])
    {
        $engine = $this->container->get('view_engine');

        if (null === $this->rendererCallable) {
            $this->rendererCallable = $engine . '\\' . $this->container->get('default_view_class');
        } else {
            $this->rendererCallable = $engine . '\\' . $this->rendererCallable;
        }
        if (is_string($this->rendererCallable)) {
            //DefaultRenderer
            $name = $this->name;
            if (!$this->container->has('view_dir')) {
                throw new \RunTimeException("No view directory set.");
            }

            $response = $this->response;
            $view = new $this->rendererCallable();
            $view->setResponse($response);
            $view->setFile($this->container->get('root_dir'), $this->container->get('view_dir'), $name, $file);
            $view->setInherit($this->container->get('inherit'));
            $response = $view->render($name, $params);
        } else {
            if (is_callable($this->rendererCallable)) {
                $this->container['response'] = $this->rendererCallable($name, $params);
            } else {
                throw new \InvalidArgumentException('the renderer set is not a callable');
            }
        }
        return $this->response;
    }

    /**
     * Generate a 404 error page using ErrorController.
     * 
     * @return Psr\Http\Message\ResponseInterface
     */
    public function show404()
    {
        $controller = $this->container->get('controller_namespace') . 'ErrorsController';
        $controller = CR::getInstance($controller, $this->container, $this);
        return call_user_func(array($controller, 'show404'));
    }

    /**
     * Redirect to another webpage.
     * @param string $url
     * @param int $code
     * @return Psr\Http\Message\ResponseInterface
     */
    public function redirect($url, $code = 302)
    {
        $response = $this->response->withStatus($code);
        $response->setHeader('location', $url);
        return $response;
    }

}
