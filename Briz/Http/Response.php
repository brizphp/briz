<?php
namespace Briz\Http;

use Psr\Http\Message\ResponseInterface;
use Briz\Http\Collections\Headers;

/**
 * Representation of an outgoing, server-side response.
 *
 * based on psr7 interface it includes and manages
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 */
class Response extends Message implements ResponseInterface
{

    /**
     * @static array
     */
    protected static $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /** @var int */
    protected $statusCode;

    /** @var string */
    protected $reasonPhrase;

    /**
     * Create new Response object
     * 
     * @param int status 
     * @param string reason phrase
     * @param HeadersInterface|null headers
     * @param StreamInterface|nul body
     */
    public function __construct($status = 200, $reasonPhrase = '', HeadersInterface $headers = null, StreamInterface $body = null)
    {
        $this->validStatusCode($status);
        $this->statusCode = $status;
        $this->reasonPhrase = $this->filterReasonPhrase($status, $reasonPhrase);
        $this->headers = $headers ? $headers : new Headers();
        $this->body = $body ? $body : new Stream(fopen('php://temp', 'r+'));
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->validStatusCode($code);
        $clone = clone $this;
        $clone->statusCode = (int) $code;
        $clone->reasonPhrase = $this->filterReasonPhrase($code, $reasonPhrase);
        return $clone;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * validate status code
     *
     * @param int $code http status code
     */
    protected function validStatusCode($code)
    {
        if (!is_numeric($code)) {
            throw new \InvalidArgumentException("Invalid status code. It must be a 3-digit integer.");
        }
        if (!isset(static::$phrases[$code])) {
            throw new \InvalidArgumentException("Invalid status code \"{$code}\".");
        }
    }

    /**
     * get correct reason phrase
     *
     * @param int $code
     * @param string $reasonPhrase
     * @return string Reason phrase
     */
    protected function filterReasonPhrase($code, $reasonPhrase)
    {
        return $reasonPhrase === '' ? static::$phrases[$code] : $reasonPhrase;
    }

    /**
     * Write data to response body
     *
     * @param string $data string to write
     */
    public function setContent($data)
    {
        $this->getBody()->write($data);
    }

    /**
     * Add Header to the response
     *
     * @param string $header
     * @param string $value
     * @param bool $replace should this header be replaced 
     *      if it exists ?
     */
    public function setHeader($header, $value, $replace = true)
    {
        $this->headers->set($header, $value, $replace);
    }

    /**
     * Alias for setContent
     */
    public function write($data)
    {
        $this->setContent($data);
    }

    /**
     * cloning pointers to objects when clones
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
        $this->body = clone $this->body;
    }

}
