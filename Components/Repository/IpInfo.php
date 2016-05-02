<?php
namespace ITF\IpInfoBundle\Components\Repository;

class IpInfo
{
    /** @var \stdClass */
    private $json;
    
    /* sample 
    stdClass Object
        (
            [ip] => 85.6.194.194
            [hostname] => 194.194.6.85.dynamic.wline.res.cust.swisscom.ch
            [city] => Basel
            [region] => Basel-City
            [country] => CH
            [loc] => 47.5761,7.5999
            [org] => AS3303 Swisscom (Switzerland) Ltd
            [postal] => 4057
        )
     */
    public function __construct($json)
    {
        $this->json = $json;
    }
    
    public function getIp()
    {
        return $this->json->ip;
    }
    
    public function getHostname()
    {
        return $this->json->hostname;
    }
    
    public function getCity()
    {
        return $this->json->city;
    }
    
    public function getRegion()
    {
        return $this->json->region;
    }
    
    public function getCountry()
    {
        return $this->json->country;
    }
    
    public function getLocation()
    {
        return $this->json->loc;
    }
    
    public function getLat()
    {
        list($lat, $lng) = explode(',', $this->json->loc);
        
        return $lat;
    }
    
    public function getLng()
    {
        list($lat, $lng) = explode(',', $this->json->loc);
        
        return $lng;
    }
    
    public function getOrganisation()
    {
        return $this->json->org;
    }
    
    public function getPostal()
    {
        return $this->json->postal;
    }
}