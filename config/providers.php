<?php
/*
 |---------------------------------------------------------------------------
 |            *******this is a system level configration*******
 |---------------------------------------------------------------------------
 | this desides what components are to be loaded to the system container
 | here you have to specify components in the following format
 |
 | format : 
 |`  'name' => 'namespace\class@method'  `
 | the method MUST be  static.
 | the values in this file just above that particular line and everything in
 | the config files application collections identities and views are ready to use.
 | and  'root_dir' and 'framework'  will be also ready.
 | if you want to pass any of these previously loaded parameters in this array
 | you can use: @name (see 'request' below to which 'server' is passed)
 | for multiple parameters use like  @name1@name2 
 |
 | 
 */
 return [
     // the server parameters. this should be an implementation Briz\Base\Interfaces\CollectionInterface
     'server'   => 'Briz\Helpers\LoadDefaults@server',
     
     // the router used for routing. this should implement Briz\Route\Interfaces\RouterInterface
     'router'   => 'Briz\Helpers\LoadDefaults@router',
     
     // the request handler. this should implement Psr\Http\Message\ServerRequestInterface
     // parameter 'server' in the container is passed to it.
     'request'  => 'Briz\Helpers\LoadDefaults@request@server',
     
     //the responce handler. this should implement Psr\Http\Message\ResponseInterface
     'response' => 'Briz\Helpers\LoadDefaults@response',
     
     // default logger is monolog.
     // parameters 'root_dir','app' and 'log_dir' from container is passed to it.
     'logger'    => 'Briz\Helpers\LoadHelpers@logger@root_dir@log_dir@app',
     
     //request method faking since most browsers can only send GET and POST
     //its value is will be null if no method changed otherwise it will be original request method.
     'FakeMethod' => 'Briz\Helpers\LoadHelpers@fakeMethod@request@fake_method@container'
     
    
 ];
