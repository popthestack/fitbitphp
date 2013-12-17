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

    public function get($resrourceFragment, $baseDate = null, $period = null, $endDate = null)
    {
        $path = sprintf('foods/log/%s/date', $resrourceFragment);
        return $this->getSeries($path, $baseDate, $period, $endDate);
    }

    /**
     * Get user foods for specific date
     *
     * {@inheritdoc}
     */
    /*
    public function getSeries($path, $baseDate = null, $period = null, $endDate = null)
    {
        $path = sprintf('foods/log/%s/date', $path);
        return parent::getSeries($path, $baseDate, $period, $endDate);
    }
    */

    /**
     * Get user calories in for a timespan
     *
     * @throws Exception
     * @param  \DateTime|string $baseDate
     * @param  string $period
     * @param  \DateTime|string $endDate
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    /*
    public function getCaloriesIn($baseDate = null, $period = null, $endDate = null)
    { 
        return $this->getSeries('caloriesIn', $baseDate, $period, $endDate);
    }
    */

    /**
     * Get user water in for a timespan
     *
     * @throws Exception
     * @param  \DateTime|string $baseDate
     * @param  string $period
     * @param  \DateTime|string $endDate
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    /*
    public function getWater($baseDate = null, $period = null, $endDate = null)
    { 
        return $this->getSeries('water', $baseDate, $period, $endDate);
    }
    */


}
