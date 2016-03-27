<?php
namespace Briz\Helpers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Load less important helper functions.
 * 
 * these can be removed if intented functionality is not required.
 */
class LoadHelpers
{

    /**
     * Create a new Monolog Logger.
     * 
     * @param string $root
     * @param string $dir
     * @param string $name
     * @return Logger
     */
    public static function logger($root, $dir, $name)
    {
        $log = new Logger($name);
        $log->pushHandler(new StreamHandler($root . '/' . $dir . '/' . $name . '.log', Logger::DEBUG));
        return $log;
    }

}
