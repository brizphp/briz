<?php
namespace Briz;

use Briz\Base\Container;
use Briz\Base\Collection;
use Briz\Base\Interfaces\IdentityInterface;
use Briz\Route\Router;
use Briz\Concrete\ControllerResolver as CR;
use Interop\Container\ContainerInterface;
use Psr\Log\LogLevel;

/**
 * The Main Class of Briz Framework.
 * 
 * you can access object of this class from container using 'framework'
 * 
 */
class App
{

    /**
     * DI container of briz
     *
     * @var Interop\Container\ContainerInterface
     */
    private $container;

    /**
     * Create new application
     * 
     * @param array $args array of container values
     */
    public function __construct(array $args = [])
    {
        $container = new Container($args);
        $this->container = $container;
        $this->initializeContainer();
        $this->initializeErrorHandler();

        if (!$this->container instanceof ContainerInterface) {
            throw new \InvalidArgumentException('Container should implement ContainerInterface');
        }
    }

    /**
     * Sets initial values to container from config files.
     * 
     * @throws \InvalidArgumentException
     */
    private function initializeContainer()
    {
        $rootdir = dirname(__DIR__);
        $this->container['framework'] = $this;
        $this->container['root_dir'] = $rootdir;
        $this->container['inherit'] = new \stdClass();
        $this->container['container'] = $this->container;
        $config = require_once('config.php');
        $configDir = ($this->container->has('config_dir')) ? $this->container->get('config_dir') : $rootdir . '/config';
        if ($this->container->has('config')) {
            $config = array_merge($config, $this->container->get('config'));
        }

        //set application variables
        $application = include_once($configDir . '/' . $config['application']);
        if (!is_array($application)) {
            throw new \InvalidArgumentException(sprintf("The config file %s does not return an array", $config['application']));
        }
        foreach ($application as $key => $value) {
            $this->container[$key] = $value;
        }
        unset($config['application']);

        //set all collections
        $collections = include_once($configDir . '/' . $config['collections']);
        if (!is_array($collections)) {
            throw new \InvalidArgumentException(sprintf("The config file %s does not return an array", $config['collections']));
        }
        foreach ($collections as $collection => $initializer) {
            if ($initializer === 'init_collection') {
                $this->container[$collection] = new Collection();
            }
        }
        unset($config['collections']);

        //set all identities
        $id = include_once($configDir . '/' . $config['identities']);
        if (!is_array($id)) {
            throw new \InvalidArgumentException(sprintf("The config file %s does not return an array", $config['identities']));
        }
        foreach ($id as $key => $className) {
            $identity = new $className;
            $this->registerIdentity($key, $identity);
        }
        unset($config['identities']);

        //view settings.
        $view = include_once($configDir . '/' . $config['view']);
        if (is_array($view)) {
            foreach ($view as $key => $value) {
                $this->container[$key] = $value;
            }
        }
        unset($config['view']);

        foreach ($config as $file) {
            $temp = include_once($configDir . '/' . $file);
            if (!is_array($temp)) {
                throw new \InvalidArgumentException(sprintf("The config file %s does not return an array", $file));
            }
            foreach ($temp as $name => $value) {
                if (is_string($value)) {
                    $set = explode('@', $value);
                    $inputSet = array_slice($set, 2);
                    foreach ($inputSet as $key => $element) {
                        $inputSet[$key] = $this->container->get($element);
                    }
                    $this->container[$name] = call_user_func_array(array($set[0], $set[1]), $inputSet);
                }
            }
        }
    }

    /**
     * add routes for the application
     *
     * @param string $name name of the route.
     * @param callable $handler caller for 
     * @param string parent 
     * @param array $args 
     */
    public function route($name, $handler, $parent = null, array $args = [])
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException("Route name must be string");
        }

        $inherit = $this->container->get('inherit');

        //add a parent.
        if ($parent !== null) {
            $args = array_merge($args, ['parent' => $parent]);
            array_push($inherit->$parent->child, $name);
        }

        if (is_callable($handler) or is_string($handler)) {
            $routeCollection = $this->container->get('router');
            $router = $routeCollection->addRoute($name, $parent);

            $this->giveContainer($router);

            $rf = new \ReflectionClass($router);
            $pl = $rf->getMethod('_setup');
            $pl->setAccessible(true);
            $cache = ($this->container->has('cache_route')) ? $this->container->get('cache_route') : false;
            $pl->invoke($router, $args, $cache);
            $this->container->get('routes')->set($name, $router);
            if (is_callable($handler)) {
                $handler($router);
            } else {
                $routePrefix = '/' . substr(strtolower($handler), 0, strlen($handler) - 10);
                CR::routeResolver($this->container->get('controller_namespace'), $router, $handler, $routePrefix);
            }
        }

        //register as a parent
        $inherit->$name = new \stdClass();
        $inherit->$name->child = [];
        $inherit->$name->parent = $parent;
    }

    /**
     * Get container
     *
     * @return Interop\Container\ContainerInterface 
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Register a new Identity
     * 
     * @param String $name
     * @param IdentityInterface $identity
     * @throws \UnexpectedValueException
     */
    public function registerIdentity($name, IdentityInterface $identity)
    {
        $id = $this->container->get('id');
        if ($id->has($name)) {
            throw new \UnexpectedValueException(sprintf("%s is already registered as an identity", $name));
        }

        $this->giveContainer($identity);

        $id->set($name, $identity);
    }

    /**
     * give container to an object using php reflection.
     *
     * it will do this only if it has a protected member with the name $container.
     * if this function is used to give container, then it will not be available in the 
     * constructor
     * 
     * @param object $object
     */
    private function giveContainer($object)
    {
        $rf = new \ReflectionClass($object);
        if ($rf->hasProperty('container')) {
            $p = $rf->getProperty('container');
            if ($p->isProtected()) {
                $p->setAccessible(true);
                $p->setValue($object, $this->container);
            }
        }
    }

    /**
     * Unregister an Identity
     * 
     * @param string $name
     */
    public function unRegisterIdentity($name)
    {
        $id = $this->container->get('id');
        if ($id->has($name)) {
            $id->remove($name);
        }
    }

    /**
     * Run the application
     */
    public function run()
    {
        $inherit = $this->container['inherit'];
        $result = [];

        $request = $this->container->get('request');
        $target = $request->getRequestTarget();
        $scriptName = $this->container->get('server')->get('SCRIPT_NAME');
        if (substr($target, 0, strlen($scriptName)) == $scriptName) {
            $url = substr($target, strlen($scriptName));
        } else {
            $scriptDir = dirname($scriptName);
            $url = substr($target, strlen($scriptDir));
            if ($scriptDir == '\\' or $scriptDir == '/') {
                $url = '/' . $url;
            }
        }
        $url = strtok($url, '?');
        $method = $request->getMethod();

        foreach ($inherit as $route => $child) {
            $routeInfo = $this->checkRoute($inherit, $route, $url, $method);
            if ($routeInfo[0] == Router::FOUND) {
                $result = $routeInfo;
                break;
            }
        }
        if (empty($result)) {
            $result = $this->getErrorResponse('show404');
        }

        if ($result[0] == Router::FOUND) {
            $out = $this->sendResponse($result);
        }
        return $out;
    }

    /**
     * gets a routeInfo array with error response.
     * 
     * @param string $action Method in the ErrorController
     * @param array $params
     * @return array
     */
    private function getErrorResponse($action, $params = [])
    {
        $routeCollection = $this->container->get('router');
        $router = $routeCollection->addRoute('errors');
        $this->giveContainer($router);
        $this->container['routes']->set('errors', $router);
        $result = [Router::FOUND, 'ErrorsController@' . $action, $params, 'route' => 'errors'];
        return $result;
    }

    /**
     * finalize and send response.
     * 
     * if the value of mode in container is no_output then it will not send response
     * instead it adds contents to reponse otherwise it will send response to output
     * @param type $routeInfo
     * @return type
     */
    private function sendResponse($routeInfo)
    {
        $routes = $this->container->get('routes');
        $route = $routes->get($routeInfo['route']);
        $handler = $routeInfo[1];
        $params = $routeInfo[2];
        $briz = $route->getBridge($params);
        if (is_a($handler, '\Closure')) {
            $response = $handler($briz);
        }
        if (is_string($handler)) {
            // format Controller@action
            $element = explode('@', $handler);
            if ($element[0] == '') {
                $element = ['ErrorsController', 'show404'];
            }
            $controller = $this->container['controller_namespace'] . $element[0];

            $controller = CR::getInstance($controller, $this->container, $briz);
            $count = count($element);
            if ($count < 2) {
                $action = 'index';
            } else {
                $action = $element[1];
            }
            $arguments = CR::resolveMethod($action, $this->container);
            $params = array_merge($arguments, $params);
            $response = call_user_func_array(array($controller, $action), $params);
        }
        if (!is_a($response, 'Psr\Http\Message\ResponseInterface')) {
            $response = $this->container->get('response');
        }
        if (!$this->container->has('mode')) {
            $this->container['mode'] = '';
        }
        if ($this->container->get('mode') != 'no_output') {
            $version = $response->getProtocolVersion();
            $code = $response->getStatusCode();
            $reason = $response->getReasonPhrase();
            $this->setStatusCode($version, $code, $reason);
            $this->setOutputHeaders($response->getHeaders());
            $this->writeToOutputStream($response->getBody());
        }
        return $response;
    }

    /**
     * Sets the status code of http header.
     * 
     * @param string $version
     * @param string $code
     * @param string $reason
     */
    private function setStatusCode($version, $code, $reason)
    {
        if (!headers_sent()) {
            header(sprintf(
                            'HTTP/%s %s %s', $version, $code, $reason
            ));
        }
    }

    /**
     * add headers from response object to output.
     * 
     * @param array $headers
     */
    private function setOutputHeaders(array $headers = [])
    {
        if (!headers_sent()) {
            foreach ($headers as $header => $values) {
                //standard header format
                $str = explode('-', $header);
                $header = ucfirst($str[0]);
                unset($str[0]);
                foreach ($str as $s) {
                    $header = $header . '-' . ucfirst($s);
                }
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $header, $value), false);
                }
            }
        }
    }

    /**
     * Write respose body to output stream.
     * 
     * @param Psr\Http\Message\StreamInterface $body
     */
    private function writeToOutputStream($body)
    {
        if ($body != null) {
            $body->rewind();
            $out = fopen('php://output', 'w');
            $chunckSize = $this->container->get('output_chunk_size');
            while (!$body->eof()) {
                fwrite($out, $body->read($chunckSize));
            }
        }
    }

    /**
     * check for route matches.
     * 
     * @param object $inherit
     * @param string $route
     * @param string $url
     * @param string $method
     * @return Array
     */
    private function checkRoute($inherit, $route, $url, $method)
    {
        $routes = $this->container->get('routes');
        $current = $routes->get($route);
        if (!$this->verifyIdentity($current->getName())) {
            return([Router::NOT_FOUND]);
        }
        $result = $current->run($method, $url);
        if ($result[0] == Router::FOUND) {
            # check for childs
            $parent = $inherit->$route;
            $result = array_merge($result, ['route' => $route]);
            foreach ($parent->child as $child) {
                $out = $this->checkRoute($inherit, $child, $url, $method);
                if ($out[0] == Router::FOUND) {
                    $result = $out;
                    $result = array_merge($result, ['route' => $child]);
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Error Handling functions.
     */
    private function initializeErrorHandler()
    {
        $errorHandler = function ($severity, $message, $file, $line) {
            $levelStrings = array(
                E_ERROR => 'E_ERROR',
                E_WARNING => 'E_WARNING',
                E_PARSE => 'E_PARSE',
                E_NOTICE => 'E_NOTICE',
                E_CORE_ERROR => 'E_CORE_ERROR',
                E_CORE_WARNING => 'E_CORE_WARNING',
                E_COMPILE_ERROR => 'E_COMPILE_ERROR',
                E_COMPILE_WARNING => 'E_COMPILE_WARNING',
                E_USER_ERROR => 'E_USER_ERROR',
                E_USER_WARNING => 'E_USER_WARNING',
                E_USER_NOTICE => 'E_USER_NOTICE',
                E_STRICT => 'E_STRICT',
                E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
                E_DEPRECATED => 'E_DEPRECATED',
                E_USER_DEPRECATED => 'E_USER_DEPRECATED',
            );
            $logLevels = array(
                E_ERROR => LogLevel::CRITICAL,
                E_WARNING => LogLevel::WARNING,
                E_PARSE => LogLevel::ALERT,
                E_NOTICE => LogLevel::NOTICE,
                E_CORE_ERROR => LogLevel::CRITICAL,
                E_CORE_WARNING => LogLevel::WARNING,
                E_COMPILE_ERROR => LogLevel::ALERT,
                E_COMPILE_WARNING => LogLevel::WARNING,
                E_USER_ERROR => LogLevel::ERROR,
                E_USER_WARNING => LogLevel::WARNING,
                E_USER_NOTICE => LogLevel::NOTICE,
                E_STRICT => LogLevel::NOTICE,
                E_RECOVERABLE_ERROR => LogLevel::ERROR,
                E_DEPRECATED => LogLevel::NOTICE,
                E_USER_DEPRECATED => LogLevel::NOTICE,
            );
            if ($this->container->has('logger')) {
                $logger = $this->container->get('logger');
                $logString = (isset($levelStrings[$severity])) ? $levelStrings[$severity] : "unknown PHP Error";
                $logLevel = (isset($logLevels[$severity])) ? $logLevels[$severity] : LogLevel::CRITICAL;
                $logger->log(
                        $logLevel, $logString . ': ' . $message, array('code' => $severity, 'message' => $message, 'file' => $file, 'line' => $line)
                );
            }
            $ErrorTexts = array(
                E_ERROR => 'Fatal Error',
                E_WARNING => 'Warning',
                E_PARSE => 'Parsing Error',
                E_NOTICE => 'Notice',
                E_CORE_ERROR => 'Core Error',
                E_CORE_WARNING => 'Core Warning',
                E_COMPILE_ERROR => 'Compile Error',
                E_COMPILE_WARNING => 'Compile Warning',
                E_USER_ERROR => 'User Error',
                E_USER_WARNING => 'User Warning',
                E_USER_NOTICE => 'User Notice',
                E_STRICT => 'Runtime Notice',
                E_RECOVERABLE_ERROR => 'Recoverable Error',
                E_DEPRECATED => 'Deprecated Warning',
                E_USER_DEPRECATED => 'Deprecated Warning',
            );
            if ((
                    ($severity & error_reporting()) !== $severity) or
                    $this->container->get('display_errors') === false) {
                return;
            }
            $mode = ($this->container->has('mode')) ? $this->container->get('mode') : '';
            $this->container['mode'] = 'no_output';
            $severity = (isset($ErrorTexts[$severity])) ? $ErrorTexts[$severity] : 'Unknown Error';
            $routeInfo = $this->getErrorResponse('errorHandler', [$severity, $message, $file, $line]);
            $this->container['response'] = $this->sendResponse($routeInfo);
            $this->container['mode'] = $mode;
        };

        set_error_handler($errorHandler);

        $exceptionHandler = function ($e) {
            if ($this->container->has('logger')) {
                $logger = $this->container->get('logger');
                $logger->log(
                        LogLevel::ERROR, sprintf('Uncaught Exception %s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()), array('exception' => $e)
                );
            }

            if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
                if ($this->container->get('display_errors') === false) {
                    return;
                }
                $routeInfo = $this->getErrorResponse('exceptionHandler', [$e]);
                $this->sendResponse($routeInfo);
                exit(1);
            }
        };
        set_exception_handler($exceptionHandler);
        $shutdownHandler = function () use ($errorHandler) {
            $last_error = error_get_last();
            if (isset($last_error) &&
                    ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
                $errorHandler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
            }
        };
        register_shutdown_function($shutdownHandler);
    }

    /**
     * Verify if all identities are matched using name.
     * 
     * @param sring $name
     * @return boolean
     */
    private function verifyIdentity($name)
    {
        $id = $this->container->get('id')->all();
        foreach ($id as $key => $identity) {
            if (!$identity->checkByComponent($name)) {
                return false;
            }
        }
        return true;
    }
}
