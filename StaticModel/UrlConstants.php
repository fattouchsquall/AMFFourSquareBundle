<?php

/**
 * @package AMFFourSquareBundle
 * @subpackage StaticModel
 * @author Mohamed Amine Fattouch <amine.fattouch@gmail.com>
 */

namespace AMF\FourSquareBundle\StaticModel;

/**
 * Define constants for variours urls.
 * 
 * @package AMFFourSquareBundle
 * @subpackage StaticModel
 * @author Mohamed Amine Fattouch <amine.fattouch@gmail.com>
 */
class UrlConstants
{
    const URL_BASE = "https://api.foursquare.com/";
    
    const URL_AUTHENTICATION = "https://foursquare.com/oauth2/authenticate";
    
    const URL_TOKEN = "https://foursquare.com/oauth2/access_token";
    
    const URL_GOOGLE_MAPS = "http://maps.googleapis.com/maps/api/geocode/json";
}

?>
