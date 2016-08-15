<?php
namespace Briz\Route;

use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

/**
 * Route Management functions.
 * 
 * Management of individual routes including add routes and dispaching
 */
class Route extends RouteCollector
{

    protected $dispatcher;

    /**
     * Creates a new Route Manager
     * @param string $name
     * @param string $generatorType
     * @param string $dispatcherType
     */
    public function __construct(
        $name,
        $generatorType = 'FastRoute\\DataGenerator\\GroupCountBased',
        $dispatcherType = 'FastRoute\\Dispatcher\\GroupCountBased'
    ) {
        $routeParser = new Std();
        $dataGenerator = new $generatorType;
        $this->dispatcher = $dispatcherType;
        parent::__construct($name, $routeParser, $dataGenerator);
    }

    /**
     * Uses FastRoute dispacher and appends renderer to it.
     * @param callable|string $renderer
     * @param string $method
     * @param string $uri
     * @return array
     */
    public function dispatch($renderer, $method, $uri)
    {
        $dispatch = new $this->dispatcher($this->getData());
        $routeInfo = $dispatch->dispatch($method, $uri);
        array_push($routeInfo, $renderer);
        return $routeInfo;
    }
}
