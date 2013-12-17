<?php

namespace Fitbit;

class FoodTimeSeriesGateway extends TimeSeriesEndpointGateway {

    /**
     * Dynamically pass methods to the default connection.
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

    /**
     * generic get method for time series calls
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
        $path = sprintf('foods/log/%s/date', $fragment);
        return $this->getSeries($path, $baseDate, $period, $endDate);
    }

}
