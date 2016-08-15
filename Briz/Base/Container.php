<?php
namespace Briz\Base;

use Interop\Container\ContainerInterface;

/**
 * This is our DI Container Class 
 * 
 * It is designed to store any values that should be avaiable
 * througout the application.
 * you are free to store any values to it.
 */
class Container implements \ArrayAccess, ContainerInterface
{

    /**
     * Container
     * @var array 
     */
    private $container = array();

    /**
     * 
     * @param array $values initial values
     */
    public function __construct(array $values = array())
    {
        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf("the value for '%s' is not available in container", $id));
        }
        return $this->offsetGet($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * set a parameter to the container.
     * 
     * @param string $offset the key for container
     * @param mixed $value the value stored
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * check if a value exists in the container.
     * 
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * unsets a parameter from container.
     * 
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Get a value from container.
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}
