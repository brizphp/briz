<?php
namespace Briz\Http;

use Psr\Http\Message\UriInterface;

/**
 * Uri.
 * 
 * Value object representing a URI.
 */
class Uri implements UriInterface
{

    /** @var string */
    protected $scheme;

    /** @var string */
    protected $host;

    /** @var string */
    protected $path;

    /** @var string */
    protected $query;

    /** @var string */
    protected $fragment;

    /** @var string|null */
    protected $port;

    /** @var string */
    protected $user;

    /** @var string */
    protected $password;

    /**
     * supported Url Schemes  
     * @var array
     */
    protected $validSchems = [
        'http' => true,
        'https' => true,
        '' => true
    ];

    /**
     * Default Port Numbers
     * @var array
     */
    protected $defaultPorts = [
        'http' => 80,
        'https' => 443
    ];

    /**
     * Create a new Uri object
     *
     * @param string $scheme
     * @param string $host
     * @param string $path
     * @param string $query
     * @param string $fragment
     * @param string|null $port
     * @param string $user
     * @param string $password
     */
    public function __construct(
    $scheme = "", $host = "", $path = "", $query = "", $fragment = "", $port = null, $user = "", $password = ''
    ) {
        $this->checkScheme($scheme);
        $this->checkHost($host);
        $this->checkPort($port);
        $this->checkPath($path);
        $this->checkQuery($query);
        $this->checkPort($port);
        $this->scheme = $scheme;
        $this->host = $host;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $host = $this->getHost();
        $userinfo = $this->getUserInfo();
        $authority = ($userinfo ? $userinfo . '@' : '') . $host;
        $port = $this->getPort();
        $scheme = $this->getScheme();
        if (isset($this->defaultPorts[$scheme])) {
            if ($port != $this->defaultPorts[$scheme]) {
                $authority .= ':' . $port;
            }
        } else {
            $authority .= ':' . $port;
        }
        return $authority;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        if (empty($this->user)) {
            return "";
        } elseif (!empty($this->password)) {
            return $this->user . ':' . $this->password;
        }
        return $this->user;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @return string The URI host.
     */
    public function getHost()
    {
        return strtolower($this->host);
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        if ($this->port === null) {
            $default = $this->defaultPorts;
            if (isset($default[$this->scheme])) {
                $this->port = $default[$this->scheme];
            }
        }
        return $this->port;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return self A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {
        $this->checkScheme($scheme);
        return $this->cloneIt('scheme', $scheme);
    }

    /**
     * Check for a valid scheme
     * @param string $scheme The scheme to use with the new instance.
     * @return bool
     */
    protected function checkScheme($scheme)
    {
        if (!isset($this->validSchems[$scheme])) {
            throw new \InvalidArgumentException('Invalid Uri Scheme');
        }
        return true;
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user The user name to use for authority.
     * @param null|string $password The password associated with $user.
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password ? $password : '';
        return $clone;
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     * @return self A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $this->checkHost($host);
        return $this->cloneIt('host', $host);
    }

    /**
     * Placeholder for children who want to add some checks.
     * host names can have very crazy forms in some applcations.
     * so omiting it now
     * @return bool
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    protected function checkHost($host)
    {
        return true;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *     removes the port information.
     * @return self A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        $this->checkPort($port);
        return $this->cloneIt('port', $port);
    }

    /**
     * Check for valid port number
     * 
     * @param int|null $port
     * @return bool
     * @throws \InvalidArgumentException for invalid ports.
     */
    protected function checkPort($port)
    {
        if ($port === null) {
            return true;
        }
        if (!(is_numeric($port) && $port >= 1 && $port <= 65535)) {
            throw new \InvalidArgumentException('Invalid Port');
        }
        return true;
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     * @return self A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        $this->checkPath($path);
        return $this->cloneIt('path', $path);
    }

    /**
     * Check for valid path
     *
     * @param string $path The path to use with the new instance.
     * @return bool.
     * @throws \InvalidArgumentException for invalid paths.
     */
    protected function checkPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Invalid Path');
        }
        return true;
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     * @return self A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        $this->checkQuery($query);
        return $this->cloneIt('query', $query);
    }

    /**
     * Checks for valid  query string
     * 
     * @param string $query The query string to use with the new instance.
     * @return bool
     * @throws \InvalidArgumentException for invalid query strings.
     */
    protected function checkQuery($query)
    {
        if (!is_string($query)) {
            throw new \InvalidArgumentException('Invalid Query');
        }
        return true;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     * @return self A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        return $this->cloneIt('fragment', $fragment);
    }

    /**
     * Helper function to clone
     * 
     * @param $name
     * @param $value
     */
    private function cloneIt($name, $value)
    {
        $clone = clone $this;
        $clone->$name = $value;
        return $clone;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @return string
     */
    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();
        if (empty($authority)) {
            $path = preg_replace("#/+#", "/", $path, 1);
        } elseif ($path[0] != '/') {
            $path = '/' . $path;
        }

        return ($scheme ? $scheme . ':' : '') .
                ($authority ? '//' . $authority : '') .
                $path .
                ($query ? '?' . $query : '') .
                ($fragment ? '#' . $fragment : '');
    }
}
