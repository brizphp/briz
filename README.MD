# Briz  PHP Framework

Briz is a simple but powerful PHP framework for creating web applications.You can Write powerful web applications with multiple faces. It is efficient for creating anything from simple API to Large web applications.

With Briz you can easily extend your web applications  from a simple API to large web application with less code or can create an api for large web applications without touching much code .in briz nothing is strict. you can write your code in the way you want.

## Documentation

You can find latest documentation at [Documentation page](http://briz.readthedocs.org)

## Installation

You can clone this repository or use composer.

```bash
$ composer create-project briz/briz target-dir
```

this will create a copy of Briz at the location target-dir. edit it to match with your location.
next point your web server root to the `www` directory.
or run it in using PHP's built in Development server.
```bash
$ cd www
$ php -S localhost:8000
```
now in your browser use `localhost:8000` to access the website

## Basic Usage

Lets start with creating a simple two page website.

```php
<?php

require './vendor/autoload.php';

$app = new Briz\App();

$app->route("web", function($router){
	//Route HTTP GET '/'
    $router->get('/',function($b){
		   $b->response->write('hello world');
    });
    //Route HTTP GET '/{anyname}' it will math with
    // anything like /briz and /haseeb etc.
    $router->get('/{name}',function($b){
    	$data = 'hello'.$b->args['name'];
    	$b->response->write($data);
    });
});
$app->run();
```

If you want to create bigger websites Briz has views and controllers. more details at [Documentation Page](http://briz.readthedocs.org)

## Features

- ### Powerful Routing and Route inheritance
Routes can inherit other routes just like in a programming language by setting a parent.

- ### Identity
It is a new way for identifying things. Together with route inheritance it makes applications easy to extend.

- ### Fully built on PSR-7
This project was started with full PSR-7 support in mind. Briz can run with any PSR-7 Implementations or Adapters.

more on [Documentation Page](http://briz.readthedocs.org)

## Feature Requests

if you like to have any new features, you are welcome. new ideas makes anything upto date and perfect. please Use GitHub Issue tracker. but mention it is a feature request. thanks for helping

## Security

security has high priority. If you find any security issues in Briz  please email to haseebeqx@yahoo.com with details instead of using issue tracker.
If your reported security vulnerability made an update in the code your name  will be added to the contributors list if you agree to do so.

## Contributing

if you like to contribute please follow PSR-2 coding standard if code is inside Briz namespace and test your code well. 
Thank you for considering contributing to Briz.

## License

Briz framework is licensed under the [MIT license](LICENCE)
