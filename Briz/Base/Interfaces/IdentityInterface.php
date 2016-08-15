<?php
namespace Briz\Base\Interfaces;

/**
 * Identity Interface.
 */
interface IdentityInterface
{
    
    /**
     * Check whether the conditions for identity are satisfied.
     * 
     * This function should return whether required conditions for a
     * identity match is occured.
     * 
     *
     * @return bool
     */
    public function identify($key, $value);
    
    /**
     * Register a new param to check identity
     * 
     * this method collets a value to be checked by the identify() method.
     * it should be cappable of setting multiple values to a key.
     * it should register identity with respect to a component name reprecenting 
     *    from where it is registered.
     */
    public function addIdentity($component, $key, $value);
    
    /**
     * Unregister a key from identity register
     *
     * this method removes a key and all its associated values from collection 
     * from checking using method identity()
     * 
     * @param string $key 
     */
    public function removeIdentity($component, $key);
    
    /**
     * Unregister a component from identity register
     *
     * this method removes all keys and all its associated values from collection 
     * by a component name
     * 
     * @param string $component 
     */
    public function removeComponent($component);
    
    /**
     * Check identity of a key in a component.
     *
     * it returns true if all values are matched
     *
     * @param string $component
     * @param string $key
     * @return bool 
     */
    public function checkByKey($component, $key);
    
    /**
     * Check identity of all registered keys in a component.
     *
     * it returns true if all keys are matched
     *
     * @param string $component
     * @return bool 
     */
    public function checkByComponent($component);
}
