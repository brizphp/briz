<?php

use Briz\App;

// Prevent session cookies
ini_set('session.use_cookies', 0);



// Enable Composer autoloader
/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';
// Register test classes
$autoloader->addPsr4('Briz\Tests\\', __DIR__);
