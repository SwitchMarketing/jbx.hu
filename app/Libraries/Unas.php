<?php

namespace App\Libraries;

use Exception;

class Unas {
    
    /**
     * error
     *
     * @var mixed
     */
    protected static $error;

    
    /**
     * query
     * 
     * @var array
     */
    protected static $query;

    /**
     * ttl
     * 
     * cache ttl
     *
     * default 31556926 (1 year)
     * 
     * @var int
     */
    protected static $ttl = 31556926;

    
    /**
     * client
     *
     * @return mixed
     */
    public static function client() 
    {
        return service('curlrequest', [
			'baseURI' => getenv('UNAS_API_BASE_URI'),
		]);
    }


    /**
     * login
     *
     * @return mixed
     */
    public static function login()
    {   
        $request = '<?xml version="1.0" encoding="UTF-8" ?>
        <Params>
            <ApiKey>'.getenv('UNAS_API_KEY').'</ApiKey>
            <WebshopInfo>true</WebshopInfo>
        </Params>';        

        return self::_get_resource('login', $request);
    }
    
    /**
     * categories
     *
     * @return mixed
     */
    public static function categories($token, bool $cache = true)
    {
        $request = '<?xml version="1.0" encoding="UTF-8" ?>
        <Params>            
            <ContentType>minimal</ContentType>            
        </Params>';

        return self::getResource('getCategory', $request, $token, $cache);

    }

    /**
     * products
     *
     * @return mixed
     */
    public static function products($token, bool $cache = true)
    {
        $request = '<?xml version="1.0" encoding="UTF-8" ?>
        <Params>    
            <LimitNum>2000</LimitNum>
            <ContentType>full</ContentType>              
        </Params>';

        return self::getResource('getProduct', $request, $token, $cache);

    }

    /**
     * getResource
     *
     * @param  mixed $path
     * @param  mixed $query
     * @param  mixed $cache
     * @param  mixed $cache_as
     * @return mixed
     */
    public static function getResource($path, $request, $token = null, bool $cache = true)
    {

        if( !$cache ) return self::_get_resource($path, $request, $token);

        if (! $item = cache($path)) {
            $item = self::_get_resource($path, $request, $token);
            cache()->save($path, $item, self::$ttl);
        }
        
        return $item;
    }
    
    /**
     * request
     *
     * @param  mixed $path
     * @param  mixed $request
     * @param  mixed $token
     * @return void
     */
    private static function _get_resource($path, $request, $token = null)
    {

        try {

            $payload = [                
                'headers' => [                    
                    'Content-Type' => 'text/xml'
                ],
                'body' => $request
            ];

            if($token) {
                $payload['headers']['Authorization'] = "Bearer {$token}";
            }            

            $response = self::client()->request('POST', $path, $payload);     

            // print_r($response);
                        
            if( ! ($response->getStatusCode() == 200) )
                throw new Exception($response->getBody()->error->message);
            
            
            // XML válasz feldolgozása, JSON konvertálása
            // és visszaadása tömbként
            $xml = simplexml_load_string($response->getBody(), null, LIBXML_NOCDATA);
            return json_decode(json_encode($xml), true);

        } catch (Exception $e) {
            self::$error = $e->getMessage();
        }

    }

    /**
     * getError
     *
     * @return string
     */
    public static function getError()
    {
        return self::$error ?? 'Unknown error occurred.';
    }

} 