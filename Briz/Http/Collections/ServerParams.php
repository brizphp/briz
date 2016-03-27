<?php
namespace Briz\Http\Collections;

use Briz\Base\Collection;
use Briz\Http\Collections\Interfaces\ServerParamsInterface;
/**
 * Collection reprecenting $_SERVER.
 */
class ServerParams extends Collection implements ServerParamsInterface
{

    /**
     * get a value from collection
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = '')
    {
        return parent::get($key, $default);
    }

}
