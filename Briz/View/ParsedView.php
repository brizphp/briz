<?php
namespace Briz\View;

/**
 * ParsedView
 * 
 * Deault View which converts array or object to variable and generates response
 * from an output file
 */
class ParsedView extends BaseView
{

    /**
     * 
     * @param string $name name of the router
     * @param array|object $params parameters to be passed to the view
     * @return Psr\Http\Message\ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function render($name, $params = [])
    {
        if ((null !== $this->response) and ( null !== $this->file)) {
            $file_name = $this->viewPath . '.view.php';

            //check if the file exits in parent directory
            if (!file_exists($file_name)) {
                if (null === $this->inherit->$name->parent) {
                    throw new \InvalidArgumentException(
                        sprintf("View file '%s' not found in %s", 
                            $this->file,
                            $this->rootDir.'/'.$this->viewDir.'/'.$name));
                }
                $name = $this->inherit->$name->parent;
                $this->setFile($this->rootDir, $this->viewDir, $name, $this->file);
                return $this->render($name, $params);
            }
            $body = function() use($params, $file_name) {
                try {
                    ob_start();
                    if (is_object($params)) {
                        $params = json_encode($params);
                        $params = json_decode($params, true);
                    }
                    extract($params);
                    require($file_name);
                    return ob_get_clean();
                } catch (Exception $e) {
                    ob_end_clean();
                    throw $e;
                }
            };
            $body = $body();

            $this->response->write($body);
            return $this->response;
        }
    }

}
