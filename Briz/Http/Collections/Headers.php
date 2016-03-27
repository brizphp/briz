<?php
namespace Briz\Http\Collections;

use Briz\Base\Collection;
use Briz\Http\Collections\Interfaces\HeadersInterface;

class Headers extends Collection implements HeadersInterface
{

    /**
     * Http headers can have more than one values
     * so setter is changed
     * 
     * @param string $header
     * @param string $value
     * @param bool $replace should this header be replaced if it exists
     */
    public function set($header, $value, $replace = true)
    {
        //for convieniene
        $header = $this->convertToUnderscore($header);
        if ($replace) {
            if (!is_array($value)) {
                $value = [$value];
            }
            parent::set($header, $value);
        } else {
            $oldValues = $this->get($header, []);
            $value = is_array($value) ? $value : [$value];
            $this->set($header, array_merge($oldValues, array_values($value)));
        }
    }

    /**
     * Get header value
     * @param string $header key
     * @param mixed  $default defaut value to return
     * @return array
     */
    public function get($header, $default = [])
    {
        $header = $this->convertToUnderscore($header);
        return parent::get($header, $default);
    }

    /**
     * Get content-type header
     * 
     * @return string|null 
     */
    public function getContentType()
    {
        return $this->get('Content_Type', null)[0];
    }

    /**
     * Get content-length header
     *
     * @return int
     */
    public function getContentLength()
    {
        return $this->get('Content_Length', 0)[0];
    }

    /**
     * convert string to lowercase and '-' to '_'. 
     * 
     * @param string $header
     */
    private function convertToUnderscore($header)
    {
        return strtolower(preg_replace('/-/', '_', $header));
    }

}
