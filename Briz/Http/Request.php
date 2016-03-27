<?php
namespace Briz\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Briz\Base\Collection;
use Briz\Http\Collections\Interfaces\HeadersInterface;
use Briz\Http\Collections\Interfaces\ServerParamsInterface;

/**
 * Representation of an outgoing, client-side request.
 * HTTP Request class
 * based on PSR 7
 */
class Request extends message implements ServerRequestInterface
{

    /**
     * Http request method
     *
     * @var string 
     */
    protected $method;

    /**
     * Uri object
     *
     * @var UriInterface
     */
    protected $uri;

    /** @var HeadersInterface */
    protected $headers;

    /** @var string */
    protected $requestTarget;

    /** @var ServerParams */
    protected $serverParams;

    /** @var array */
    protected $cookieParams;

    /** @var array */
    protected $queryParams;

    /** @var array */
    protected $uplodedFiles;

    /** @var bool|object|array */
    protected $parsedBody = false;

    /** @var Collection */
    protected $attributes;

    /**
     * Array of parsers
     * @var array ('type' => callable)
     */
    protected $parsers = [];

    /**
     * Array of valid HTTP methods
     *
     * @var array 
     */
    protected $validMethods = [
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

    /**
     * Create a new HTTP request object
     *
     * @param string $method http request method
     * @param UriInterface $uri Request Uri object
     * @param string $version http version
     * @param Headers $headers Collection of headers
     * @param StreamInterface $body 
     * @param Collection $uplodedFiles collection of uploaded files
     * @param string version
     * @param array attributes 
     */
    public function __construct(
    $method, UriInterface $uri, HeadersInterface $headers, StreamInterface $body, ServerParamsInterface $params, array $cookie, array $uplodedFiles = [], $version = "1.1", array $attributes = []
    )
    {
        $this->method = $this->validateMethod($method);
        $this->uri = $uri;
        $this->protocolVersion = $this->validateVersion($version);
        $this->headers = $headers;
        $this->body = $body;
        $this->serverParams = $params;
        $this->cookieParams = $cookie;
        $this->uplodedFiles = $uplodedFiles;
        $this->attributes = new Collection($attributes);
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if (isset($this->requestTarget)) {
            return $this->requestTarget;
        }
        if (null === $this->uri) {
            return '/';
        }
        $query = $this->uri->getQuery();
        $target = $this->uri->getPath() .
                ( $query ? '?' . $query : '');

        if ($target === '') {
            $target = '/';
        }
        $this->requestTarget = $target;
        return $target;
    }

    /**
     * Return an instance with the specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * @param mixed $requestTarget
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    /**
     * Validate the HTTP method
     *
     * @param  null|string $method
     * @return string
     * @throws \InvalidArgumentException 
     */
    protected function validateMethod($method)
    {
        if (null === $method) {
            return '';
        }
        if (!is_string($method)) {
            throw new \InvalidArgumentException('Unsupported HTTP method. It must be a string.');
        }
        $method = strtoupper($method);
        if (!isset($this->validMethods[$method])) {
            throw new \InvalidArgumentException("Unsupported HTTP method. \"{$method}\" provided.");
        }
        return $method;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * @param string $method Case-sensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = $this->ValidateMethod($method);
        return $clone;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * You can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`. When `$preserveHost` is set to
     * `true`, this method interacts with the Host header in the following ways:
     *
     * @param UriInterface $uri New request URI to use.
     * @param bool $preserveHost Preserve the original state of the Host header.
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;
        $host = $uri->getHost();
        if ($preserveHost) {
            if (empty($this->uri->getHost()) && (!$this->hasHeader('Host') || $host === null)) {
                $clone->headers->set('Host', $host);
            }
        } elseif (empty($host)) {
            $clone->headers->set('Host', $host);
        }
        return $clone;
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams->all();
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     * @return self
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        if (is_array($this->queryParams)) {
            return $this->queryParams();
        }
        if (null === $this->uri) {
            return array();
        }
        $params = $this->uri->getQuery();
        parse_str($params, $this->queryParams);
        return $this->queryParams;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * @param array $query Array of query string arguments.
     * @return self
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->uplodedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array An array tree of UploadedFileInterface instances.
     * @return self
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uplodedFiles = $uploadedFiles;
        return $clone;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return null|array|object The deserialized body parameters, if any.
     *     These will typically be an array or object.
     * @throws 
     */
    public function getParsedBody()
    {
        if ($this->parsedBody) {
            return $this->parsedBody;
        }
        $type = $this->headers->getContentType();
        $type = strtolower($type);
        $body = (string) $this->getBody();
        //if there is a user defined one, use it instead of builtin
        if (isset($this->parsers[$type])) {
            $this->parsedBody = $this->parsers[$type]($body);
        } elseif ($type == 'application/x-www-form-urlencoded' or $type == 'multipart/form-data') {
            $this->parsedBody = $_POST;
        } elseif ($type == 'application/json') {
            $this->parsedBody = json_decode($body, true);
            return $this->parsedBody;
        } elseif ($type == 'application/xml' or $type == 'text/xml') {
            $this->parsedBody = simplexml_load_string($body);
            return $this->parsedBody;
        }
        if (!$this->parsedBody) {
            return null;
        }
        if (!is_array($this->parsedBody) and ! is_object($this->parsedBody)) {
            $this->parsedBody = false;
            throw new \RuntimeException('the registered parser should only return null or object or array ');
        }
        return $this->parsedBody;
    }

    /**
     * Overrides a builtin parser or add a new parser
     *
     * this function not part of psr7 standard
     *
     * @param string $type the media type 
     * @param callable $callable the parser definition
     * 
     * @throws \InvalidArgumentException if an invalid format is provided.
     */
    public function registerParser($type, $callable)
    {
        if (is_string($type) and is_callable($callable)) {
            $type = strtolower($type);
            $this->parsers[$type] = $callable;
        } else {
            throw new \InvalidArgumentException(
            'Invalid registerParser format. use registerParser(\'string\', callable) '
            );
        }
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *     typically be in an array or object.
     * @return self
     * @throws \InvalidArgumentException if an unsupported argument type is
     *     provided.
     */
    public function withParsedBody($data)
    {
        if ((null !== $data) && !is_object($data) && !is_array($data)) {
            throw new \InvalidArgumentException('Parsed body value must be an array, an object, or null');
        }

        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes->all();
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->get($name, $default);
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return self
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes->set($name, $value);

        return $clone;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return self
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        $clone->attributes->remove($name);

        return $clone;
    }

    /**
     * cloning pointers to objects when clones
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->attributes = clone $this->attributes;
        $this->body = clone $this->body;
    }

}
