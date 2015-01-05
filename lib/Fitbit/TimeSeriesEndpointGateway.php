<?php

namespace Fitbit;
use DateTime;

class TimeSeriesEndpointGateway extends EndpointGateway {


    /**
     * base fragment for the instantiated resource uri
     * 
     * @var sting
     */
    protected static $format;

    /**
     * create a uri fragment from a method name
     *
     * @param string $method
     */
    public function fragment($method)
    {
        $method = substr($method, 3);
        $fragment = strtolower($method[0]) . substr($method, 1);
        return sprintf(static::$format, $fragment);
    }

    /**
     * Get user time series
     *
     * @throws Exception
     * @param  string $fragment
     * @param  \DateTime|string $baseDate
     * @param  string $period
     * @param  \DateTime|string $endDate
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function get($fragment, $baseDate = null, $period = null, $endDate = null)
    {
        $date1 = $baseDate ?: 'today';
        $date2 = ($period) ? $period : ($endDate) ?: '1d';

        if ($date1 instanceof Datetime)
            $date1 = $date1->format("Y-m-d");

        if ($date2 instanceof Datetime) 
            $date2 = $date2->format("Y-m-d");

        $endpoint = sprintf('user/%s/%s/%s/%s', $this->userID, $fragment, $date1, $date2);
        return $this->makeApiRequest($endpoint);
    }

    /**
     * Dynamically pass methods to get.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $fragment = $this->fragment($method);
        array_unshift($parameters, $fragment);        
        return call_user_func_array(array($this, 'get'), $parameters);   
    }

}
