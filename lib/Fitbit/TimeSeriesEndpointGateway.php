<?php

namespace Fitbit;

class TimeSeriesEndpointGateway extends EndpointGateway {


    /**
     * create a uri fragment from a method name
     *
     * @param string $method
     */
    public function fragment($method)
    {
        $method = substr($method, 3);
        return strtolower($method[0]) . substr($method, 1);
    }

    /**
     * Get user foods for specific date
     *
     * @throws Exception
     * @param  \DateTime|string $baseDate
     * @param  string $period
     * @param  \DateTime|string $endDate
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getSeries($path, $baseDate = null, $period = null, $endDate = null)
    {
        $baseDate = $baseDate ?: 'today';
        $end = ($period) ? $period : ($endDate) ?: '1d';

        if ($baseDate instanceof Datetime)
            $baseDate = $baseDate->format("Y-m-d");

        if ($end instanceof Datetime) 
            $end = $end->format("Y-m-d");

        $endpoint = sprintf('user/%s/%s/%s/%s', $this->userID, $path, $baseDate, $end);
        var_dump($endpoint);
        return $this->makeApiRequest($endpoint);
    }

}
