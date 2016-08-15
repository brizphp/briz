<?php
namespace Briz\Base\Interfaces;

/**
 * Collection Interface
 */
interface CollectionInterface
{
    
    /**
     * Set or change a  value for an element in the collection
     * this is an assignment of new value into the collection
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value);
    
    /**
     * Get a value for a key
     * @param string $key
     * @param mixed $default is return value if key dosn't exists
     * @return mixed if key exists then its value otherwise null or default
     */
    public function get($key, $default = null);
    
    /**
     * Check if a key exists in the collection
     * @param string $key
     * return bool
     */
    public function has($key);
    
    /**
     * Remove all the items from the collecton
     */
    public function clear();
    
    /**
     * Merge an array with the collection.
     * it can be another collection or array
     * @param array $array the array to merge
     */
    public function merge(array $array);
    
    /**
     * Get all values from the collection
     * @return array  all items in the collection
     */
    public function all();
    
    /**
     * Remove an item from the collection
     * @param mixed key
     */
    public function remove($key);
    
    /**
     * Get the number of items in the array
     * @return int number of items
     */
    public function count();
    
    /**
     * Replace a the entire collection with another array
     * @param array $array
     */
    public function replace(array $array);
    
    /**
     * Execute a callback function for each item in the collection
     * @param callable $callback
     */
    public function each($callback);
    
    /**
     * Check if the collection is empty
     * return bool 
     */
    public function isEmpty();
}
