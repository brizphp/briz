<?php
namespace Briz\View;

/**
 * Generate a JSON response based on given input.
 */
class JsonView extends BaseView
{

    /**
     * Generate a json response with params.
     * 
     * @param string|null $name
     * @param array $params
     * @return Psr\Http\Message\ResponseInterface
     */
    public function render($name = null, $params = [])
    {
        if (null !== $this->response) {
            $this->response->setHeader('Content-Type', 'application/json');
            $body = json_encode($params);
            $this->response->write($body);
            return $this->response;
        }
    }

}
