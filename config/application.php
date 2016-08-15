<?php
return [
    /*
    |-----------------------------------------------------------
    | Application wide available static values should be defined here
    |-----------------------------------------------------------
    |
    */
    
     /* 
     --------------System Level Configuration -------------
     */
    //Application namespace
    'app'                   => 'MyApp',
    
    //Application name
    'app_name'              => 'MyApp',
    
    //Controller namespace
    'controller_namespace'  => 'MyApp\\Controllers\\',
    
    //directory for logging errors
    'log_dir'       => 'logs',
    
    //if sets to true it will display errors ONLY IF application error_reporting is set.
    'display_errors' => true,
    
    //output chunk size 
    'output_chunk_size' => 4096,
    /* 
    -----------------User Level Configuration --------------
    */
    
    //http request method faking string.
    'fake_method' => 'X-HTTP-Method-Override'
];
