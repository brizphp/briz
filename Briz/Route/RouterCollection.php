<?php
namespace Briz\Route;

use Briz\Base\Collection;

/**
 * Collection of Routers
 */
class RouterCollection extends Collection
{

    /**
     * add a new Router to collection
     * 
     * @param string $name
     * @param string $parent
     * @return \Briz\Route\Router
     */
    public function addRoute($name, $parent = null)
    {
        $router = new Router($name, $parent);
        $this->set($name, $router);
        return $router;
    }

}
