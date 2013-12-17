<?php

namespace Fitbit;

class ActivityTimeSeriesGateway extends TimeSeriesEndpointGateway {

    /**
     * base fragment for this resources uri
     * 
     * @var sting
     */
    protected static $format = 'activities/%s/date';

    /**
     * convert to trcker only fragment
     * 
     * @param string $fragment
     * @return string
     */
    protected function trackerOnlyFragment($fragment)
    {   
        return str_replace('activities', 'activities/tracker', $fragment);
    }

    /**
     * extended get to all for tracker only resource calls
     *
     * @throws Exception
     * @param  string $fragment
     * @param  bool $tracker
     * @param  \DateTime|string $baseDate
     * @param  string $period
     * @param  \DateTime|string $endDate
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function get($fragment, $tracker = false, $baseDate = null, $period = null, $endDate = null)
    {
        $fragment = ($tracker) ? $this->trackerOnlyFragment($fragment) : $fragment;
        return parent::get($fragment, $baseDate, $period, $endDate);
    }

    /**
     * extended call, to ensure methods without tracker
     * have tracker set to false
     * 
     * {@inheritdoc}
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, array('getCaloriesBMR'))) $parameters[0] = false; 
        return parent::__call($method, $parameters);        
    }


}
