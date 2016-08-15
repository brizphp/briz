<?php
namespace MyApp\Controllers;

use Briz\Concrete\BController;

/**
 * Errors Controller.
 *
 * This is the space to define error pages.
 * 
 * WARNING: DO not Delete This File.
 * 
 */
class ErrorsController extends BController
{
    /**
     * 404 Not Found.
     *
     * Define your 404 function by editing this method.
     * Default 404 error does not use briz renderer
     * instead it sends output directly
     */
    public function show404()
    {
        //set response status code to 404
        $this->response = $this->response->withStatus('404');
        $target = $this->request->getRequestTarget();
        $content = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head>
         <title>404 Not Found</title>
        </head><body>
        <h1>Not Found</h1>
        <p>The requested URL '.$target.'  was not found on this server.</p>
        <hr>
        </body></html>';
        
        $this->response->write($content);
        return $this->response;
    }
    
    /**
     * Error Handler.
     */
    public function errorHandler($severity, $message, $filepath, $line, $code='')
    {
        if (!empty($code)) {
            $this->response = $this->response->withStatus($code);
        }

        return $this->renderer(
             'errors',
             ['severity' => $severity, 'message' => $message, 'filepath'=> $filepath, 'line' => $line]
         );
    }
    
    public function exceptionHandler($exception)
    {
        $this->response = $this->response->withStatus('500');
        return $this->renderer('exception', ['exception' => $exception]);
    }
}
