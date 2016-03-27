<?php
namespace Briz\Helpers;

use Briz\Http\Collections\ServerParams;
use Briz\Http\Collections\Headers;
use Briz\Http\Uri;
use Briz\Http\Request;
use Briz\Http\Response;
use Briz\Http\Stream;
use Briz\Http\UploadedFile;
use Briz\Route\RouterCollection;

/**
 * Load Important System Components.
 */
class LoadDefaults
{

    /**
     * generate ServerParams from $_SERVER.
     * 
     * @return ServerParams
     */
    public static function server()
    {
        return new ServerParams($_SERVER);
    }

    /**
     * Generate RouteCollection object.
     * 
     * generate router collection.
     * 
     * @return RouteCollection
     */
    public static function router()
    {
        return new RouterCollection();
    }

    /**
     * 
     * @param ServerParams $server
     * @return Request
     */
    public static function request(ServerParams $server)
    {
        //method
        $method = $server->get('REQUEST_METHOD');

        //Uri
        if (null !== $server->get('HTTPS') &&
                ($server->get('HTTPS') == 'on' || $server->get('HTTPS') == 1) ||
                (null !== $server->get('HTTP_X_FORWARDED_PROTO')) &&
                $server->get('HTTP_X_FORWARDED_PROTO') == 'https'
        ) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }
        if ($server->has('HTTP_HOST')) {
            $host = $server->get('HTTP_HOST');
        } else {
            $host = $server->get('SERVER_NAME', '');
        }
        $port = (int) $server->get('SERVER_PORT', 80);
        $path = strtok($server->get('REQUEST_URI'), '?');
        if (!is_string($path)) {
            $path = '';
        }
        $queryString = $server->get('QUERY_STRING', '');
        $fragment = '';
        $username = $server->get('PHP_AUTH_USER', '');
        $password = $server->get('PHP_AUTH_PW', '');

        $uri = new Uri($scheme, $host, $path, $queryString, $fragment, $port, $username, $password);

        //headers
        $header = [];
        foreach ($server->all() as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $header[substr($name, 5)] = $value;
            }
        }
        $header['CONTENT_TYPE'] = $server->get('CONTENT_TYPE');
        $header['CONTENT_LENGTH'] = $server->get('CONTENT_LENGTH');
        $header['PHP_AUTH_USER'] = $server->get('PHP_AUTH_USER');
        $header['PHP_AUTH_PW'] = $server->get('PHP_AUTH_PW');
        $header['PHP_AUTH_DIGEST'] = $server->get('PHP_AUTH_DIGEST');
        $header['AUTH_TYPE'] = $server->get('AUTH_TYPE');
        $headers = new Headers($header);
        //body
        $body = fopen('php://temp', 'w+');
        stream_copy_to_stream(fopen('php://input', 'r'), $body);
        rewind($body);
        $body = new Stream($body);

        //files
        $files = [];
        if (isset($_FILES)) {
            foreach ($_FILES as $file) {
                $files[] = new UploadedFile($file['tmp_name'], $file['size'], $file['error'], $file['name'], $file['type']
                );
            }
        }

        //http version
        $version = $server->get('SERVER_PROTOCOL');
        $version = explode('/', $version);
        $version = (isset($version[1])) ? $version[1] : 1.0;

        //cookies
        $cookies = $_COOKIE;

        $request = new Request($method, $uri, $headers, $body, $server, $cookies, $files, $version);

        return $request;
    }

    /**
     * Generate Default Response Object
     * @return Response
     */
    public static function response()
    {
        $response = new Response();
        return $response;
    }

}
