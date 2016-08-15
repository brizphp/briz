<?php
namespace Briz\Helpers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Load less important helper functions.
 * 
 * these can be removed if intented functionality is not required.
 */
class LoadHelpers
{

    /**
     * Create a new Monolog Logger.
     * 
     * @param string $root
     * @param string $dir
     * @param string $name
     * @return Logger
     */
    public static function logger($root, $dir, $name)
    {
        $log = new Logger($name);
        $log->pushHandler(new StreamHandler($root . '/' . $dir . '/' . $name . '.log', Logger::DEBUG));
        return $log;
    }
    
    /**
     * change request method based on a header.
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param string $mock
     * @param Interop\Container\ContainerInterface $container
     * @return bool whether the method is faked?.
     */
    public static function fakeMethod($request, $mock, $container)
    {
        $headerLine = $request->getHeaderLine($mock);
        if (!empty($headerLine)) {
            $headers = explode(',', $headerLine);
            try {
                $original = $request->getMethod();
                $request = $request->withMethod($headers[0]);
                $container['request'] = $request;
                return $original;
            } catch (\Exception $e) {
            }
        } else {
            $body = $request->getParsedBody();
            if ($body === null) {
                return null;
            }
            if (is_object($body)) {
                $body = json_encode($body);
                $body = json_decode($body, true);
            }
            if (isset($body[$mock])) {
                try {
                    $original = $request->getMethod();
                    $request = $request->withMethod($body[$mock]);
                    $container['request'] = $request;
                    return $original;
                } catch (\Exception $ex) {
                }
            }
            return null;
        }
    }
}
