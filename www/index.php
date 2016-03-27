<?php
require '../vendor/autoload.php';
$app = new Briz\App();
  
$app->route("web", function($router){
    $router->get('/',function($briz){
		$data = 'hello';
        $briz->renderer('hello',['title'=>'Welcome to Briz Framework',
            'content'=>'Thanks for using Briz Framework. You can Learn More at Our Documentaion Page ',
            'link'=>'http://briz.readthedocs.org/en/latest/'
            ]);
      });
});

//mobile is a child of web intended to work as json api. last parameter 'web' is parent name.
$app->route('mobile',function($router){
	
	// Identify using header identity.
	// if a header With X-Request-Cli with value android encountered then it will use this router
	$router->identify('header','X-Request-Cli','mobile');
	
	//now the responses will be rendererd as json
    $router->setRenderer('JsonView');
},'web');
  $app->run();