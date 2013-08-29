<?php

namespace Fitbit;

class SleepGateway extends EndpointGateway {

    /**
     * Get user sleep log entries for specific date
     *
     * @throws Exception
     * @param  \DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getSleep($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        return $this->makeApiRequest('user/' . $this->userID . '/sleep/date/' . $dateStr);
    }

    /**
     * Log user sleep
     *
     * @throws Exception
     * @param \DateTime $date Sleep date and time (set proper timezone, which could be fetched via getProfile)
     * @param string $duration Duration millis
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logSleep(\DateTime $date, $duration)
    {
        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        $parameters['startTime'] = $date->format('H:i');
        $parameters['duration'] = $duration;

        return $this->makeApiRequest('user/-/sleep', 'POST', $parameters);
    }

    /**
     * Delete user sleep record
     *
     * @throws Exception
     * @param string $id Activity log id
     * @return bool
     */
    public function deleteSleep($id)
    {
        return $this->makeApiRequest('user/-/sleep/' . $id, 'DELETE');
    }
}
