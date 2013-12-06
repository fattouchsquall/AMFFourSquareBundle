<?php

/**
 * @package FourSquareBundle
 * @subpackage Controller
 * @author Mohamed Amine Fattouch <amine.fattouch@gmail.com>
 */

namespace AMF\FourSquareBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for venues.
 * 
 * @package FourSquareBundle
 * @subpackage Controller
 * @author Mohamed Amine Fattouch <amine.fattouch@gmail.com>
 */
class VenueController extends ContainerAware
{

    /**
     * Finds places around an area.
     * 
     * @param string $latitude
     * @param string $longitude
     * 
     * @return Response
     */
    public function searchAction($latitude, $longitude)
    {
        $request = $this->container->get('request');
        if ($request->isXmlHttpRequest())
        {
            $latLng     = (float) $latitude . "," . (float) $longitude;
            $parameters = array("ll" => $latLng, 'radius' => 100000);

            $foursquareService = $this->container->get('amf_foursquare.service');
            $placesJson        = $foursquareService->performPublicRequest("venues/search",
                                                           $parameters);
        
            return new Response($placesJson);
        }
        
        return new Response();
    }
    
    /**
     * Adds a new place.
     * 
     * 
     * @return Response
     */
    public function addAction($name)
    {
        $request = $this->container->get('request');
        
        $parameters = array('name' => $name);
        $foursquareService = $this->container->get('amf_foursquare.service');
        $placesJson        = $foursquareService->performPublicRequest("venues/add",
                                                           $parameters);
        return new Response($placesJson);
    }

}
