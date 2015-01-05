<?php

namespace Fitbit;

class FoodTimeSeriesGateway extends TimeSeriesEndpointGateway {


    /**
     * base fragment for this resources uri
     * 
     * @var sting
     */
    protected static $format = 'foods/log/%s/date';

}
