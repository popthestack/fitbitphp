<?php

namespace Fitbit;

class WaterGateway extends EndpointGateway {

    /**
     * Get user water log entries for specific date
     *
     * @throws Exception
     * @param  \DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getWater($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        return $this->makeApiRequest('user/-/foods/log/water/date/' . $dateStr);
    }

    /**
     * Log user water
     *
     * @throws Exception
     * @param \DateTime $date Log entry date (set proper timezone, which could be fetched via getProfile)
     * @param string $amount Amount in ml/fl oz (as set with setMetric) or waterUnit
     * @param string $waterUnit Water Unit ("ml", "fl oz" or "cup")
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logWater(\DateTime $date, $amount, $waterUnit = null)
    {
        $waterUnits = array('ml', 'fl oz', 'cup');

        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        $parameters['amount'] = $amount;
        if (isset($waterUnit) && in_array($waterUnit, $waterUnits))
            $parameters['unit'] = $waterUnit;

        return $this->makeApiRequest('user/-/foods/log/water', 'POST', $parameters);
    }

    /**
     * Delete user water record
     *
     * @throws Exception
     * @param string $id Water log id
     * @return bool
     */
    public function deleteWater($id)
    {
        return $this->makeApiRequest('user/-/foods/log/water/' . $id, 'DELETE');
    }

}
