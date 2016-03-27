<?php
namespace Briz\Concrete;

/**
 * Resolve Controller Using Reflection.
 */
class ControllerResolver
{

    /**
     * class name.
     * @var string 
     */
    private static $className = "";

    /**
     * Get instance of a controller.
     * 
     * resolves a controller by resolving all dependencies
     *  specified by 'Use' tags for controller in docblock of class.
     * 
     * @param string $className
     * @param Interop\Container\ContainerInterface $container
     * @param Briz\Route $bridge
     * @return object resolved controller
     */
    public static function getInstance($className, $container, $bridge)
    {
        self::$className = $className;
        $reflection = new \ReflectionClass($className);
        $object = $reflection->newInstanceWithoutConstructor();

        $resolver = function($name, $value) use($reflection, $object) {
            if ($reflection->hasProperty($name)) {
                $property = $reflection->getProperty($name);
                $property->setAccessible(true);
                $property->setValue($object, $value);
            } else {
                $object->$name = $value;
            }
        };

        //setup must have values first
        $resolver('briz', $bridge);

        $resolver('container', $container);

        $resolver('request', $container->get('request'));

        $resolver('response', $container->get('response'));


        $doc = $reflection->getDocComment();
        self::resolver($doc, $resolver, $container);

        $construct = $reflection->getConstructor();
        if (null !== $construct) {
            $construct->invoke($object);
        }
        return $object;
    }

    /**
     * Resolves a method
     * 
     * Resolves a method by resolving all dependencies
     *  specified by 'Use' tags for the method.
     * @param string $method
     * @param Interop\Container\ContainerInterface $container
     * @return array
     */
    public static function resolveMethod($method, $container)
    {
        $className = self::$className;
        $reflection = new \ReflectionClass($className);
        $result = [];
        $resolver = function($name, $value)use(&$result, $container) {
            array_push($result, $value);
        };
        try {
            $method = $reflection->getMethod($method);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
            sprintf(
                    "The method '%s' can not be found in the controller '%s'", $method, $className
            )
            );
        }
        $doc = $method->getDocComment();
        self::resolver($doc, $resolver, $container);
        return $result;
    }

    /**
     * executes resolver callback for each Use.
     * 
     * @param string $doc
     * @param callable $resolver
     * @param Interop\Container\ContainerInterface $container
     * @throws \InvalidArgumentException
     */
    private static function resolver($doc, $resolver, $container)
    {
        $lines = explode("\n", $doc);
        foreach ($lines as $line) {
            $block = explode("@Use", $line);
            if (count($block) > 1) {
                $value = trim($block[1]);
                if (!$container->has($value)) {
                    throw new \InvalidArgumentException(
                    sprintf("The value %s must be available in container before injecting into Controller", $value)
                    );
                }
                $resolver($value, $container->get($value));
            }
        }
    }

    /**
     * Resolve Routes when route is set as a controller.
     * 
     * @param String $namespace
     * @param Briz\Route\Router $router
     * @param string $handler
     * @param string $routePrefix
     */
    public static function routeResolver($namespace, $router, $handler, $routePrefix)
    {
        $controller = $handler;
        $handler = $namespace . $handler;
        $rf = new \ReflectionClass($handler);
        $methods = $rf->getMethods();
        foreach ($methods as $method) {
            if ($method->class === $handler) {
                $doc = $method->getDocComment();
                if ($doc) {
                    $useCount = 0;
                    $lines = explode("\n", $doc);
                    foreach ($lines as $line) {
                        $use = explode("@Use", $line);
                        if (count($use) > 1) {
                            $useCount++;
                        }
                        $block = explode("@Route", $line);
                        if (count($block) > 1) {
                            preg_match('#\[(.*)\]#', $block[1], $httpMethods);
                            $httpMethods = explode(',', $httpMethods[1]);
                            $count = count($httpMethods);
                            for ($i = 0; $i < $count; $i++) {
                                $httpMethods[$i] = trim(strtoupper($httpMethods[$i]));
                            }
                        }
                    }
                    $params = $method->getNumberOfParameters();
                    $suffix = '';
                    if ($useCount < $params) {
                        $count = $params - $useCount;
                        for ($i = 0; $i < $count; $i++) {
                            $suffix = $suffix . '/{name' . $i . '}';
                        }
                    }
                    if ($method->name != 'index') {
                        $pattern = $routePrefix . '/' . $method->name . $suffix;
                    } else {
                        $pattern = $routePrefix . $suffix;
                    }
                    $action = $controller . '@' . $method->name;
                    $router->set($httpMethods, $pattern, $action);
                }
            }
        }
    }

}
