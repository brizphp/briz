<?php
  /**
   | Require the auto loader to load the files
   **/
require '../vendor/autoload.php';

/******
 * Create new Application Instance
 ****/
$app = new Briz\App();
/***
 | Add a new Router with the name 'web'
 | and add a route in it.
  */ 
$app->route("web", function($router){
	
	//Route for Http GET method.
    $router->get('/',function($briz){
		
		//renderer uses the view file hello.view.php and passes an array to it with 
		// values 'title' , 'content' and 'link'
        $briz->renderer('hello',['title'=>'Welcome to Briz Framework',
            'content'=>'Thanks for using Briz Framework. You can Learn More at Our Documentaion Page ',
            'link'=>'http://briz.readthedocs.io/en/latest/'
            ]);
      });
});

/**
 | next router is 'mobile'
 | mobile is a child of 'web' intended to work as a json API.
 | it will render everything in 'web' as JSON.
 | last parameter 'web' in this method is parent name.
 **/
$app->route('mobile',function($router){
	
	// Identify using header identity.
	// if a header With 'X-Request-Cli' with value 'mobile' encountered then it will use this router
	$router->identify('header','X-Request-Cli','mobile');
	
    //use json renderer
	//responses will be rendererd as json
    $router->setRenderer('JsonView');
},'web');

// finally run the application
  $app->run();