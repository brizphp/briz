<?php
namespace Briz\Route;

use FastRoute\Dispatcher;

/**
 * Main Routing object of the framework.
 */
class Router
{

    /**
     * Component prefix.
     */
    const PREFIX = '_Router_';
    const NOT_FOUND = Dispatcher::NOT_FOUND;
    const FOUND = Dispatcher::FOUND;

    /** @var array */
    protected $args;

    /**
     * The name of the parent.
     *
     * @var string|null
     */
    public $parent = null;

    /** @var ContainerInterfae */
    protected $container;

    /** @var string */
    protected $name;

    /** @var string */
    protected $realName;

    /** @var callable|null|string * */
    protected $renderer = null;

    /** @var array */
    protected $identities = [];
    protected $bridge;

    /**
     * 
     * @param string $name
     * @param string $parent
     */
    public function __construct($name, $parent = null)
    {
        $this->realName = $name;
        $this->name = self::PREFIX . $name;
        $this->parent = $parent;
    }

    /**
     * setup a new router.
     *
     * @param array $args array of arguments
     * @param bool $cached should this be cached.
     */
    private function _setup($args, $cached = false)
    {
        $this->cached = $cached;
        $this->args = $args;

        $routeCollector = $this->container->get('route_collector');
        if ($this->parent === null) {
            if (isset($args['dataGenerator']) and isset($args['dispatcher'])) {
                $routeCollector->set($this->name, new Route($this->name, $args['dataGenerator'], $args['dispatcher']));
            } else {
                $routeCollector->set($this->name, new Route($this->name));
            }
        } else {
            $parentRoute = $routeCollector->get(self::PREFIX . $this->parent);
            $routeCollector->set($this->name, clone $parentRoute);
            $routeCollector->get($this->name)->setName($this->name);
            $routeCollection = $this->container->get('router');
            $parent = $routeCollection->get($this->realName);
            $this->identities = $parent->getIdentity();
        }
    }

    /**
     * Get the name of the parent of this route.
     * 
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * add a get route for the pattern.
     * 
     * @param string $pattern the url pattern to be matched
     * @param string|callable $callback
     */
    public function get($pattern, $callback)
    {
        $this->set(['GET'], $pattern, $callback);
    }

    /**
     * add a post route for the pattern.
     * 
     * @param string $pattern the url pattern to be matched
     * @param string|callable $callback
     */
    public function post($pattern, $callback)
    {
        $this->set(['POST'], $pattern, $callback);
    }

    /**
     * add a PUT route for the pattern.
     * 
     * @param string $pattern the url pattern to be matched
     * @param string|callable $callback
     */
    public function put($pattern,$callback)
    {
        $this->set(['PUT'], $pattern, $callback);
    }

    /**
     * add a PATCH route for the pattern.
     * 
     * @param string $pattern the url pattern to be matched
     * @param string|callable $callback
     */
    public function patch($pattern, $callback)
    {
        $this->set(['PATCH'], $pattern, $callback);
    }
    
    /**
     * add a DELETE route for the pattern.
     * 
     * @param string $pattern the url pattern to be matched
     * @param string|callable $callback
     */
    public function delete($pattern, $callback)
    {
        $this->set(['DELETE'], $pattern, $callback);
    }

    /**
     * add an OPTIONS route for the pattern.
     * 
     * @param string $pattern the url pattern to be matched
     * @param string|callable $callback
     */
    public function options($pattern, $callback)
    {
        $this->set(['OPTIONS'], $pattern, $callback);
    }
    
    /**
     * add an all method route for the pattern.
     * 
     * adds GET,POST,PUT,PATCH,DELETE and OPTIONS route.
     * @param string $pattern the url pattern to be matched
     * @param string|callable $callback
     */
    public function any($pattern, $callback)
    {
        $this->set(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $callback);
    }

    /**
     * set a route for one or more HTTP methods.
     * 
     * @param array $methods array of methods
     * @param string $pattern the url pattern to be matched
     * @param string|callable $callback
     * @throws \InvalidArgumentException if unsupported method is passed
     */
    public function set(array $methods, $pattern, $callback)
    {
        $validMethods = [
            'CONNECT' => true,
            'DELETE' => true,
            'GET' => true,
            'HEAD' => true,
            'OPTIONS' => true,
            'PATCH' => true,
            'POST' => true,
            'PUT' => true,
            'TRACE' => true,
        ];
        foreach ($methods as $method) {
            if (!isset($validMethods[strtoupper($method)])) {
                throw new \InvalidArgumentException("Unsupported HTTP method. \"$method\" provided.");
            }
        }
        $routeCollector = $this->container->get('route_collector');
        $route = $routeCollector->get($this->name);
        $route->addRoute($methods, $pattern, $callback);
    }

    /**
     * Set a renderer for current router.
     *
     * @param callable|string $renderer The renderer class name or a closure
     */
    public function setRenderer($renderer)
    {
        if (is_callable($renderer) or is_string($renderer)) {
            $this->renderer = $renderer;
        } else {
            throw new \InvalidArgumentException("setRenderer expects string or callable");
        }
    }

    /**
     * get the renderer with this route
     * @return callable|string|null
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Set an identifier.
     *
     * You can set multiple identifiers on a router.
     * this function accepts any number of arguments.
     * but minimum number of arguments is two.
     */
    public function identify()
    {
        $identifiers = $this->container->get('id');
        $arg_list = func_get_args();

        if (empty($arg_list)) {
            throw new \BadMethodCallException("No key specified");
        }
        if (func_num_args() < 2) {
            throw new \BadMethodCallException("Minimum number of arguments for an identity check is 2");
        }
        if (!$identifiers->has($arg_list[0])) {
            throw new \InvalidArgumentException(sprintf(
                    "The key `%s` is not set as idetity.You need to set it first", $arg_list[0]));
        }

        $identity = $identifiers->get($arg_list[0]);

        if (!isset($this->identities[$arg_list[0]])) {
            $this->identities[$arg_list[0]] = $identity;
        }
        //change identity name to component name for convienience
        $arg_list[0] = $this->name;

        call_user_func_array(array($identity, 'addIdentity'), $arg_list);
    }

    /**
     * returns an array of all identities registered with this route
     * @return array
     */
    public function getIdentity()
    {
        return $this->identities;
    }

    /**
     * get the prefixed name of the router.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the unprefixed name of the router.
     * 
     * @return type
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * Checks if current route satisfies all Identities.
     *
     * @return bool
     */
    public function checkIdentity()
    {
        foreach ($this->identities as $identity) {
            if (!$identity->checkByComponent($this->name)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns fastroute dispatcher array.
     *
     * @param string $method
     * @param string $url
     * @return array routeInfo
     */
    public function run($method, $url)
    {
        $renderer = $this->getRenderer();
        $route = $this->container['route_collector']->get($this->name);
        return $route->dispatch($renderer, $method, $url);
    }

    /**
     * Returns a bridge for current route
     * @param array $args optional array of arguments. if there is a prevoiusly set bridge
     *              this value will not be considered
     * @return \Briz\Route\BridgeRoutes
     */
    public function getBridge($args = [])
    {
        if (is_a($this->bridge, 'BridgeRoutes')) {
            return $this->bridge;
        } else {
            $bridge = new BridgeRoutes($this->realName, $this->container, $this->renderer, $args);
            $this->bridge = $bridge;
            return $this->bridge;
        }
    }

}
