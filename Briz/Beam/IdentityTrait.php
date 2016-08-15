<?php
namespace Briz\Beam;

/**
 * Trait to implement Identity checking.
 */
trait IdentityTrait
{

    protected $identities = [];
    protected $idt_keyIndex = [];

    /**
     * add a new identity.
     * 
     * this function can accept any number of arguments. you need to pass 
     * minimum two arguments. 
     * @param string $name The name of the identity in container
     * @param mixed $value value to be identified
     * @param mixed $value,... additional values
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function addIdentity($name, $value)
    {
        $identifiers = $this->container->get('id');
        $arg_list = func_get_args();

        if (empty($arg_list)) {
            throw new \BadMethodCallException("No key specified");
        }
        if (func_num_args() < 2) {
            throw new \BadMethodCallException("Minimum number of arguments for an identity check is 2");
        }
        if (!$identifiers->has($name)) {
            throw new \InvalidArgumentException(sprintf(
                    "The key `%s` is not set as idetity.You need to set it first", $name));
        }

        $identity = $identifiers->get($name);

        if (!isset($this->identities[$name])) {
            $this->identities[$name] = $identity;
            $this->idt_keyIndex[$name] = [];
        }
        if (!in_array($value, $this->idt_keyIndex[$name], true)) {
            $this->idt_keyIndex[$name][] = $value;
        }
        //change identity name to component name for convienience
        $arg_list[0] = __CLASS__;

        call_user_func_array(array($identity, 'addIdentity'), $arg_list);
    }

    /**
     * Remove an added Identity.
     * 
     * @param string $name name of the identity
     * @param mixed $key the second argument passed when an idenity was added
     */
    public function removeIdentity($name, $key)
    {
        $identifiers = $this->container->get('id');
        $identity = $identifiers->get($name);
        $identity->removeIdentity(__CLASS__, $key);
        if (isset($this->idt_keyIndex[$name])) {
            $position = array_search($key, $this->idt_keyIndex[$name]);
            unset($this->idt_keyIndex[$name][$position]);
            if (empty($this->idt_keyIndex[$name])) {
                unset($this->identities[$name]);
                unset($this->idt_keyIndex[$name]);
            }
        }
    }

    /**
     * Remove all identity.
     * 
     * if a name is specified it will only remove all identities under that name
     * @param string|null $name optional name
     */
    public function removeAllIdentity($name = null)
    {
        if (null === $name) {
            foreach ($this->identities as $identity) {
                $identity->removeComponent(__CLASS__);
                $this->identities = [];
                $this->idt_keyIndex = [];
            }
        } else {
            $identifiers = $this->container->get('id');
            $identity = $identifiers->get($name);
            $identity->removeComponent(__CLASS__);
            unset($this->identities[$name]);
            unset($this->idt_keyIndex[$name]);
        }
    }

    /**
     * check if all added identities matches.
     * 
     * @return bool
     */
    public function identifyAll()
    {
        foreach ($this->identities as $identity) {
            if (!$identity->checkByComponent(__CLASS__)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Identify by name.
     * 
     * identifies everything under a name if no key is specified 
     * otherwise check for identity matched by key
     * @param string $name name of the identity
     * @param mixed|null $key second parameter when added Identity
     * @return bool
     */
    public function identifyByName($name, $key = null)
    {
        $identifiers = $this->container->get('id');
        $identity = $identifiers->get($name);
        if (null === $identity) {
            return true;
        }
        if (null === $key) {
            return $identity->checkByComponent(__CLASS__);
        } else {
            return $identity->checkByKey(__CLASS__, $key);
        }
    }
}
