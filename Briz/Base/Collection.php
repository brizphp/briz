<?php
namespace Briz\Base;

/**
 * Collection
 * 
 * Used to store collection of data in key value pairs.
 * and various operations on the collection
 */
class Collection
{

    /**
     * An array of items in the collection
     * @var array 
     */
    protected $items;

    /**
     * Create a new collection. 
     * @param  array  $array is used to prepopulate  contents
     * @return void
     */
    public function __construct(array $array = [])
    {
        $this->items = [];
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Set or change a  value for an element in the collection
     * this is an assignment of new value into the collection
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->items[$key] = $value;
    }

    /**
     * Get a value for a key
     * @param string $key
     * @param mixed $default is return value if key dosn't exists
     * @return mixed if key exists then its value otherwise null or default
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Key for a collection must be string");
        }
        if ($this->has($key)) {
            return $this->items[$key];
        }
        return $default;
    }

    /**
     * Check if a key exists in the collection
     * @param string $key
     * return bool
     */
    public function has($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException("Key for a collection must be string");
        }
        $items = $this->items;
        return isset($items[$key]);
    }

    /**
     * Check if the collection is empty
     * return bool 
     */
    public function isempty()
    {
        return empty($this->items);
    }

    /**
     * Remove all the items from the collecton
     */
    public function clear()
    {
        $this->items = array();
    }

    /**
     * Merge an array with the collection.
     * it can be another collection or array
     * @param array $array the array to merge
     */
    public function merge(array $array)
    {
        $this->items = array_merge($this->items, $array);
    }

    /**
     * Get all values from the collection
     * @return array  all items in the collection
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Remove an item from the collection
     * @param mixed $key
     */
    public function remove($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Get the number of items in the array
     * @return int number of items
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Replace a the entire collection with another array
     * @param array $array
     */
    public function replace(array $array)
    {
        $this->items = $array;
    }

    /**
     * Execute a callback function for each item in the collection
     * @param callable $callback
     * @return Collection
     */
    public function each($callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

}
