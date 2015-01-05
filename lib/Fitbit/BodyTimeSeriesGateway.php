<?php

namespace Fitbit;

class BodyTimeSeriesGateway extends TimeSeriesEndpointGateway {


    /**
     * base fragment for this resources uri
     * 
     * @var sting
     */
    protected static $format = 'body/%s/date';

}
