<?php

namespace Fitbit;

class TimeGateway extends EndpointGateway {
    /**
     * Launch TimeSeries requests
     *
     * Allowed types are:
     *            'caloriesIn', 'water'
     *
     *            'caloriesOut', 'steps', 'distance', 'floors', 'elevation'
     *            'minutesSedentary', 'minutesLightlyActive', 'minutesFairlyActive', 'minutesVeryActive',
     *            'activeScore', 'activityCalories',
     *
     *            'tracker_caloriesOut', 'tracker_steps', 'tracker_distance', 'tracker_floors', 'tracker_elevation'
     *            'tracker_activeScore'
     *
     *            'startTime', 'timeInBed', 'minutesAsleep', 'minutesAwake', 'awakeningsCount',
     *            'minutesToFallAsleep', 'minutesAfterWakeup',
     *            'efficiency'
     *
     *            'weight', 'bmi', 'fat'
     *
     * @throws Exception
     * @param string $type
     * @param  $basedate \DateTime or 'today', to_period
     * @param  $to_period \DateTime or '1d, 7d, 30d, 1w, 1m, 3m, 6m, 1y, max'
     * @return array
     */
    public function getTimeSeries($type, $basedate, $to_period)
    {

        switch ($type) {
            case 'caloriesIn':
                $path = '/foods/log/caloriesIn';
                break;
            case 'water':
                $path = '/foods/log/water';
                break;

            case 'caloriesOut':
                $path = '/activities/log/calories';
                break;
            case 'steps':
                $path = '/activities/log/steps';
                break;
            case 'distance':
                $path = '/activities/log/distance';
                break;
            case 'floors':
                $path = '/activities/log/floors';
                break;
            case 'elevation':
                $path = '/activities/log/elevation';
                break;
            case 'minutesSedentary':
                $path = '/activities/log/minutesSedentary';
                break;
            case 'minutesLightlyActive':
                $path = '/activities/log/minutesLightlyActive';
                break;
            case 'minutesFairlyActive':
                $path = '/activities/log/minutesFairlyActive';
                break;
            case 'minutesVeryActive':
                $path = '/activities/log/minutesVeryActive';
                break;
            case 'activeScore':
                $path = '/activities/log/activeScore';
                break;
            case 'activityCalories':
                $path = '/activities/log/activityCalories';
                break;

            case 'tracker_caloriesOut':
                $path = '/activities/log/tracker/calories';
                break;
            case 'tracker_steps':
                $path = '/activities/log/tracker/steps';
                break;
            case 'tracker_distance':
                $path = '/activities/log/tracker/distance';
                break;
            case 'tracker_floors':
                $path = '/activities/log/tracker/floors';
                break;
            case 'tracker_elevation':
                $path = '/activities/log/tracker/elevation';
                break;
            case 'tracker_activeScore':
                $path = '/activities/log/tracker/activeScore';
                break;

            case 'startTime':
                $path = '/sleep/startTime';
                break;
            case 'timeInBed':
                $path = '/sleep/timeInBed';
                break;
            case 'minutesAsleep':
                $path = '/sleep/minutesAsleep';
                break;
            case 'awakeningsCount':
                $path = '/sleep/awakeningsCount';
                break;
            case 'minutesAwake':
                $path = '/sleep/minutesAwake';
                break;
            case 'minutesToFallAsleep':
                $path = '/sleep/minutesToFallAsleep';
                break;
            case 'minutesAfterWakeup':
                $path = '/sleep/minutesAfterWakeup';
                break;
            case 'efficiency':
                $path = '/sleep/efficiency';
                break;


            case 'weight':
                $path = '/body/weight';
                break;
            case 'bmi':
                $path = '/body/bmi';
                break;
            case 'fat':
                $path = '/body/fat';
                break;

            default:
                return false;
        }

        return $this->makeApiRequest(sprintf('user/%s/date/%s/%s',
            $this->userID . $path,
            (is_string($basedate) ? $basedate : $basedate->format('Y-m-d')),
            (is_string($to_period) ? $to_period : $to_period->format('Y-m-d')))
        );
    }

    /**
     * Launch IntradayTimeSeries requests
     *
     * Allowed types are:
     *            'caloriesOut', 'steps', 'floors', 'elevation'
     *
     * @throws Exception
     * @param string $type
     * @param  $date \DateTime or 'today'
     * @param  $start_time \DateTime
     * @param  $end_time \DateTime
     * @return object
     */
    public function getIntradayTimeSeries($type, $date, $start_time = null, $end_time = null)
    {
        switch ($type) {
            case 'caloriesOut':
                $path = '/activities/log/calories';
                break;
            case 'steps':
                $path = '/activities/log/steps';
                break;
            case 'floors':
                $path = '/activities/log/floors';
                break;
            case 'elevation':
                $path = '/activities/log/elevation';
                break;

            default:
                return false;
        }

        return $this->makeApiRequest(sprintf('user/-%s/date/%s/1d%s',
            $path,
            (is_string($date) ? $date : $date->format('Y-m-d')),
            ((!empty($start_time) && !empty($end_time)) ? '/time/' . $start_time->format('H:i') . '/' . $end_time->format('H:i') : ''))
        );
    }
}
