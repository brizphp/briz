<?php
namespace Briz\View;

/**
 * print input as it gets.
 * 
 * it expects this input contains single dimentional array or objeect
 */
class DirectView extends BaseView
{

    /**
     * Generate a response with params.
     * 
     * @param string|null $name
     * @param array|object $params
     * @return Psr\Http\Message\ResponseInterface
     */
    public function render($name = null, $params = [])
    {
        foreach ($params as $value) {
            $this->response->write($value);
        }
        return $this->response;
    }

}
