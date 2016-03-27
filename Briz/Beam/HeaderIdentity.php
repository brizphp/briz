<?php
namespace Briz\Beam;

use Briz\Base\Identity;

/**
 * Identity for checking header values.
 */
class HeaderIdentity extends Identity
{

    /**
     * Check if a header exists in request.
     * 
     * @param string $header
     * @param string $value
     * @return bool
     */
    public function identify($header, $value)
    {
        $request = $this->container->get('request');
        $headers = $request->getHeaders();

        //make compatible with internal reprecentation. X-Header-Name to x_header_name
        $header = strtolower($header);
        $header = str_replace('-', '_', $header);

        if (isset($headers[$header])) {
            $key = $headers[$header];
            foreach ($key as $values) {
                //test if key is availabe in comma seperated values or directly
                $values = explode(',', $values);
                foreach ($values as $val){
                    if ($value == trim($val)) {
                    return true;
                }
                }
            }
            
            
        }
        return false;
    }

}
