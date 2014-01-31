<?php

/**
 * Provides an abstraction for making a requests to the Foursquare API.
 * 
 * @package AMFFourSquareBundle
 * @subpackage Service
 * @author Mohamed Amine Fattouch <amine.fattouch@gmail.com>
 */

namespace AMF\FourSquareBundle\Service;

use AMF\FourSquareBundle\StaticModel\HttpMethodConstants;
use AMF\FourSquareBundle\StaticModel\UrlConstants;

/**
 * Provides an abstraction for making a requests to the Foursquare API.
 * 
 * @package AMFFourSquareBundle
 * @subpackage Service
 * @author Mohamed Amine Fattouch <amine.fattouch@gmail.com>
 */
class FourSquare
{
    
    /**
     * @var string
     */
    protected $baseUrl;

    /** 
     * @var string
     */
    protected $clientId;

    /** 
     * @var string
     */
    protected $clientSecret;
    
    /**
     * @var string
     */
    protected $locale;

    /** 
     * @var string
     */
    protected $redirectUri;
    
    /** 
     * @var string
     */
    protected $version;

    /** 
     * @var string
     */
    protected $authenticationToken;

    
    /**
     * Constructor class.
     * 
     * @param string $clientId
     * @param string $clientSecret
     * @param string $locale
     * @param string $redirectUri
     * @param string $version
     * @param string $locale
     */
    public function __construct($clientId, 
                                $clientSecret,
                                $locale,
                                $redirectUri,
                                $version)
    {
        $this->baseUrl      = UrlConstants::URL_BASE . $version;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->locale       = $locale;
        $this->redirectUri  = $redirectUri;
        $this->version      = $version;
    }

    /**
     * Performs a request for a public resource.
     * 
     * @param string $endPoint   A particular endpoint of the Foursquare API.
     * @param array  $parameters A set of parameters to be appended to the request (defaults to empty).
     * 
     * @return string
     */
    public function performPublicRequest($endPoint, array $parameters=array())
    {
        // build the full URL
        $url = $this->baseUrl . trim($endPoint, "/");
        // append the details of client to parameters
        $parameters['client_id']     = $this->clientId;
        $parameters['client_secret'] = $this->clientSecret;
        $parameters['version']       = $this->version;
        $parameters['locale']        = $this->locale;
        
        $jsonResponse = $this->performGet($url, $parameters);
        
        return $jsonResponse;
    }

    /**
     * Performs a request for a private resource.
     * 
     * @param string  $endPoint   A particular endpoint of the Foursquare API.
     * @param array   $parameters A set of parameters to be appended to the request (defaults to empty).
     * @param boolean $isPost     Whether or not to use a POST request.
     * 
     * @return string
     */
    public function performPrivateRequest($endPoint, array $parameters=array(), $isPost=false)
    {
        $url                       = $this->baseUrl . trim($endPoint, "/");
        $parameters['oauth_token'] = $this->authenticationToken;
        $parameters['version']     = $this->version;
        $parameters['locale']      = $this->locale;
        
        if ($isPost === true)
        {
            $jsonReponse = $this->performGet($url, $parameters);
        }
        else
        {
            $jsonReponse = $this->performPost($url, $parameters);
        }
        
        return $jsonReponse;
    }

    /**
     * Performs a multiple requests for move then one private or public resource.
     * 
     * @param array   $requests A set of arrays containing the endpoint and a set of parameters.
     * @param boolean $isPost   Whether or not to use a POST request.
     * 
     * @return string
     */
    public function performMultileRequest(array $requests=array(), $isPost=false)
    {
        $url = $this->baseUrl . "multi/";
        
        $parameters                = array();
        $parameters['oauth_token'] = $this->authenticationToken;
        $parameters['version']     = $this->version;
        
        if (is_array($requests))
        {
            $requestQueries = array();
            foreach ($requests as $request)
            {
                $query = $request['endpoint'];
                
                if (array_key_exists('params', $request))
                {
                    if (!empty($request) && is_array($request['params']))
                    {
                        $query .= '?' . http_build_query($request);
                    }
                }
                
                $requestQueries[] = $query;
            }
            $parameters['requests'] = implode(',', $requestQueries);
        }
        
        if (!$isPost === true)
        {
            $jsonReponse = $this->performGet($url, $parameters);
        }
        else
        {
            $jsonReponse = $this->performPost($url, $parameters);
        }
        
        return $jsonReponse;
    }

    /**
     * Returns the response from json.
     * 
     * @param string $json The value encoded in json.
     * 
     * @throws \Exception Throws exception when the response is not valid.
     * 
     * @return string
     */
    public function getResponseFromJsonString($json)
    {
        $json = json_decode($json);
        
        if (!isset($json->response))
        {
            throw new \Exception('Invalid response');
        }
        
        if (!isset( $json->meta->code ) || 200 !== $json->meta->code ) 
        {
            throw new \Exception( 'Invalid response' );
        }
        
        return $json->response;
    }
    
    /**
     * Performs a Get request.
     * 
     * @param string $url        The base url.
     * @param array  $parameters A set of parameters to be appended to the request (defaults to empty).
     * 
     * @return string
     */
    protected function performGet($url, array $parameters=array())
    {
        // create url for Get request
        $url         = $this->buildGetUrl($url, $parameters);
        $jsonReponse = $this->performRequest($url, $parameters, HttpMethodConstants::HTTP_GET);
        return $jsonReponse;
    }

    /**
     * Performs a Post request.
     * 
     * @param string $url        The base url.
     * @param array  $parameters A set of parameters to be appended to the request (defaults to empty).
     * 
     * @return string
     */
    protected function performPost($url, array $parameters=array())
    {
        $jsonReponse = $this->performRequest($url, $parameters, HttpMethodConstants::HTTP_POST);
        return $jsonReponse;
    }

    /**
     * Performs the request to Foursquare API via cURL.
     * 
     * @param string $url         The base url.
     * @param array  $parameters  A set of parameters to be appended to the request (defaults to empty).
     * @param string $requestType The type of request (POST or GET).
     * 
     * @return string
     */
    protected function performRequest($url, array $parameters=array(), $requestType=HttpMethodConstants::HTTP_GET)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
        else
        {
            // Handle the useragent like we are Google Chrome
            curl_setopt($ch, CURLOPT_USERAGENT,
                    'Moamf/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.X.Y.Z Safari/525.13.');
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $acceptLanguage[] = "Accept-Language:" . $this->locale;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $acceptLanguage);
        // populate the data for POST
        if ($requestType === HttpMethodConstants::HTTP_POST)
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (!empty($parameters)) 
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            }
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Performs a request to google maps API to get the lat and lng of an address accepted by google maps.
     * 
     * @param string $address An address string accepted by the google maps api.
     * 
     * @return array|boolean
     */
    public function geolocateAddress($address)
    {
        $params       = array("address" => $address, "sensor" => "false");
        $jsonResponse = $this->performGet(UrlConstants::GOOGLE_MAPS, $params);
        $reponse      = json_decode($jsonResponse);
        if ($reponse->status === "ZERO_RESULTS")
        {
            return false;
        }
        else
        {
            return array('latitude' => $reponse->results[0]->geometry->location->lat, 'longitude' => $reponse->results[0]->geometry->location->lng);
        }
    }

    /**
     * Creates a complete url with each parameter as a GET parameter.
     * 
     * @param string $url        The base URL to append the query string to.
     * @param array  $parameters The parameters to pass to the URL via Get method.
     * 
     * @return string
     */
    protected function buildGetUrl($url, array $parameters=array())
    {
        if (!empty($parameters))
        {
            $parametersForGet = http_build_query($parameters);
            $url              = trim($url) . '?' . $parametersForGet;
        }
        
        return $url;
    }

    /**
     * Returns the url for the Foursquare web authentication page.
     * 
     * @param string $redirect The configured redirect uri for the provided client credentials.
     * 
     * @return string
     */
    public function retrieveAuthenticationUrl($redirectUri=null)
    {
        if (!isset($redirectUri) && 0 === strlen($redirectUri))
        {
            $redirectUri = $this->redirectUri;
        }
        
        $parameters = array("client_id" => $this->clientId, 
                        "response_type" => "code", 
                        "redirect_uri"  => $redirectUri);
        
        return $this->buildGetUrl($this->authenticateUrl, $parameters);
    }

    /**
     * getToken
     * Performs a request to Foursquare for a user token, and returns the token, while also storing it
     * locally for use in private requests
     * 
     * @param string $code     The 'code' parameter provided by the Foursquare webauth callback redirect
     * @param string $redirect The configured redirect uri for the provided client credentials.
     * 
     * @return string|boolean
     */
    public function getToken($code, $redirectUri=null)
    {
        if (!isset($redirectUri) && 0 === strlen($redirectUri))
        {
            $redirectUri = $this->redirectUri;
        }
        
        $parameters = array("client_id"     => $this->clientId,
                        "client_secret" => $this->clientSecret,
                        "grant_type"    => "authorization_code",
                        "redirect_uri"  => $redirectUri,
                        "code"          => $code);
        
        $result = $this->performGet(UrlConstants::TOKEN, $parameters);
        $json   = json_decode($result);

        // Petr Babicka Check if we get token
        if (property_exists($json, 'access_token'))
        {
            $this->setAuthenticationToken($json->access_token);
            return $json->access_token;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Setter for authentication token.
     * 
     * @param string $authenticationToken 
     */
    public function setAuthenticationToken($authenticationToken)
    {
        $this->authenticationToken = $authenticationToken;
    }
    
    /**
     * Setter for redirectUri.
     * 
     * @param string $redirectUri
     * 
     * @return FourSquare.
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
        
        return $this;
    }
    
    /**
     * Getter for redirectUri.
     * 
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

}
