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

        return self::request('login', $request);
    }
    
    /**
     * categories
     *
     * @return mixed
     */
    public static function categories($token)
    {
        $request = '<?xml version="1.0" encoding="UTF-8" ?>
        <Params>            
            <ContentType>minimal</ContentType>            
        </Params>';

        return self::request('getCategory', $request, $token);

    }

    /**
     * products
     *
     * @return mixed
     */
    public static function products($token)
    {
        $request = '<?xml version="1.0" encoding="UTF-8" ?>
        <Params>    
            <LimitNum>100</LimitNum>
            <ContentType>normal</ContentType>              
        </Params>';

        return self::request('getProduct', $request, $token);

    }

    private static function request($path, $request, $token = null)
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

            if( ! ($response->getStatusCode() == 200) )
                throw new Exception($response->getBody()->error->message);
            
            return simplexml_load_string($response->getBody(), null, LIBXML_NOCDATA);

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
        return self::$error;
    }

} 