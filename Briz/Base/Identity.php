<?php
namespace Briz\Base;

use Briz\Base\Interfaces\IdentityInterface;

/**
 * Identities are classes for adding an identity to something.
 *
 * This class provides methods to help identify based on a property.
 */
abstract class Identity implements IdentityInterface
{

    /**
     * The system level container
     * 
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The new identity Register
     * 
     * @var array
     */
    protected $register = [];

    /**
     * Check whether the conditions for identity are satisfied
     * 
     * This function should return whether required conditions for a
     * identity match is occured
     *
     * @param mixed $key key to be checked
     * @param mixed $value value to be checked
     * @return bool
     */
    public abstract function identify($key, $value);

    /**
     * Register a new param to check identity.
     * 
     * this method collets a value to be checked by the identify() method.
     * it should be cappable of setting multiple values to a key
     * 
     * @param string $component The name of the component where you use it.
     * @param string $key
     * @param mixed $value
     */
    public function addIdentity($component, $key, $value)
    {
        if (!isset($this->register[$component])) {
            $this->register[$component] = [];
        }
        if (!isset($this->register[$component][$key])) {
            $this->register[$component][$key] = [];
        }
        $this->register[$component][$key][] = $value;
    }

    /**
     * Unregister a key from identity register
     *
     * this method removes a key and all its associated values from collection 
     * from checking using method identity()
     * @param string $compoent component name
     * @param string $key key to remove
     */
    public function removeIdentity($component, $key)
    {
        if (isset($this->register[$component][$key])) {
            unset($this->register[$component][$key]);
        }
        if(empty($this->register[$component])){
            unset($this->register[$component]);
        }
    }

    /**
     * Unregister a component from identity register
     *
     * this method removes all keys and all its associated values from collection 
     * by a component name
     * 
     * @param string $component 
     */
    public function removeComponent($component)
    {
        if (isset($this->register[$component])) {
            unset($this->register[$component]);
        }
    }

    /**
     * Check identity of all registered keys in a component.
     *
     * it returns true if all keys are matched.
     * or if there is no component with that name.
     *
     * @param string $component
     * @return bool 
     */
    public function checkByComponent($component)
    {
        if (isset($this->register[$component])) {

            foreach ($this->register[$component] as $name => $key) {
                foreach ($key as $value) {
                    if (!$this->identify($name, $value)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Check identity of a key in a component.
     *
     * it returns true if all values are matched
     * or there is no such key in internal register.
     *
     * @param string $component
     * @param string $key
     * @return bool 
     */
    public function checkByKey($component, $key)
    {
        if (isset($this->register[$component][$key])) {
            foreach ($this->register[$component][$key] as $value) {
                if (!$this->identify($key, $value)) {
                    return false;
                }
            }
        }
        return true;
    }

}
