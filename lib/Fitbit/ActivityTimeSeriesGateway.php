<?php

namespace Fitbit;

class ActivityTimeSeriesGateway extends EndpointGateway {

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $method = str_replace('get','', $method);
        $method = strtolower($method[0]) . substr($method, 1);
        array_unshift($parameters, $method);

        if (in_array($method, array('caloriesBMR'))) $parameters[1] = false; 
        
        return call_user_func_array(array($this, 'get'), $parameters);   
    }

    public function get($resrourceFragment, $tracker = false, $baseDate = null, $period = null, $endDate = null)
    {
        $resrourceFragment = ($tracker) ? 'tracker/' . $resrourceFragment : $resrourceFragment;
        $path = sprintf('activities/%s/date', $resrourceFragment);
        return $this->getSeries($path, $baseDate, $period, $endDate);
    }


}
