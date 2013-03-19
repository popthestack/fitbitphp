<?php
/**
 * FitbitPHP v.0.71. Basic Fitbit API wrapper for PHP using OAuth
 *
 * Note: Library is in beta and provided as-is. We hope to add features as API grows, however
 *       feel free to fork, extend and send pull requests to us.
 *
 * - https://github.com/heyitspavel/fitbitphp
 *
 *
 * Date: 2012/04/02
 * Requires OAuth 1.0.0, SimpleXML
 * @version 0.71 ($Id$)
 */
namespace Fitbit;

class Api
{
    protected $service;

    protected $oauthToken;
    protected $oauthSecret;

    protected $userId = '-';
    protected $responseFormat;

    /**
     * @param string $consumer_key Application consumer key for Fitbit API
     * @param string $consumer_secret Application secret
     * @param int $debug Debug mode (0/1) enables OAuth internal debug
     * @param string $user_agent User-agent to use in API calls
     * @param string $response_format Response format (json or xml) to use in API calls
     */
    public function __construct($consumer_key, $consumer_secret, $callbackUrl = null, $responseFormat = 'json', \OAuth\Common\Storage\TokenStorageInterface $storageAdapter = null)
    {
        if (!in_array($responseFormat, array('json', 'xml')))
        {
            throw new \Exception("Reponse format must be one of 'json', 'xml'");
        }

        // If callback url wasn't set, use the current url
        if ($callbackUrl == null) {
            $uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
            $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
            $currentUri->setQuery('');
            $callbackUrl = $currentUri->getAbsoluteUri();
        }

        $this->responseFormat = $responseFormat;

        $factory = new \OAuth\ServiceFactory();

        $credentials = new \OAuth\Common\Consumer\Credentials(
            $consumer_key,
            $consumer_secret,
            $callbackUrl
        );

        if ($storageAdapter == null)
        {
            $storageAdapter = new \OAuth\Common\Storage\Session();
        }

        $this->service = $factory->createService('FitBit', $credentials, $storageAdapter);
    }

    public function isAuthorized() {
        return $this->service->getStorage()->hasAccessToken();
    }

    /**
    * Authorize the user
    *
    */
    public function initSession() {

        if (empty($_SESSION['fitbit_Session']))
            $_SESSION['fitbit_Session'] = 0;


        if (!isset($_GET['oauth_token']) && $_SESSION['fitbit_Session'] == 1)
            $_SESSION['fitbit_Session'] = 0;


        if ($_SESSION['fitbit_Session'] == 0) {

            $token = $this->service->requestRequestToken();
            $url = $this->service->getAuthorizationUri(['oauth_token' => $token->getRequestToken()]);

            $_SESSION['fitbit_Session'] = 1;
            header('Location: ' . $url);
            exit;

        } else if ($_SESSION['fitbit_Session'] == 1) {

            $token = $this->service->getStorage()->retrieveAccessToken();
            // This was a callback request from fitbit, get the token
            $this->service->requestAccessToken(
                $_GET['oauth_token'],
                $_GET['oauth_verifier'],
                $token->getRequestTokenSecret() );

            $_SESSION['fitbit_Session'] = 2;

            return 1;

        }
    }

    /**
     * Reset session
     *
     * @return void
     */
    public function resetSession()
    {
        // TODO: Need to add clear to the interface for phpoauthlib
        $this->service->getStorage()->clearToken();
        unset($_SESSION["fitbit_Session"]);
    }

    /**
     * Set Fitbit userId for future API calls
     *
     * @param  $userId 'XXXXX'
     * @return void
     */
    public function setUser($userId)
    {
        $this->userId = $userId;
    }

    protected function verifyToken()
    {
        if(!$this->isAuthorized()) {
            throw new \Exception("You must be authorized to make requests");
        }
    }

    /**
     * API wrappers
     *
     */
    public function getProfile()
    {
        $response = $this->service->request('user/'.$this->userId.'/profile.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Update user profile
     *
     * @throws Exception
     * @param string $gender 'FEMALE', 'MALE' or 'NA'
     * @param DateTime $birthday Date of birth
     * @param string $height Height in cm/inches (as set with setMetric)
     * @param string $nickname Nickname
     * @param string $fullName Full name
     * @param string $timezone Timezone in the format 'America/Los_Angeles'
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function updateProfile($gender = null, $birthday = null, $height = null, $nickname = null, $fullName = null, $timezone = null)
    {
        $parameters = array();
        if (isset($gender))
            $parameters['gender'] = $gender;
        if (isset($birthday))
            $parameters['birthday'] = $birthday->format('Y-m-d');
        if (isset($height))
            $parameters['height'] = $height;
        if (isset($nickname))
            $parameters['nickname'] = $nickname;
        if (isset($fullName))
            $parameters['fullName'] = $fullName;
        if (isset($timezone))
            $parameters['timezone'] = $timezone;

        $response = $this->service->request('user/'.$this->userId.'/profile.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }

    /**
     * Get user activities for specific date
     *
     * @throws Exception
     * @param  DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getActivities($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        $response = $this->service->request('user/'.$this->userId.'/activities/date/'.$dateStr.".".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user recent activities
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getRecentActivities()
    {
        $response = $this->service->request('user/-/activities/recent.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user frequent activities
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFrequentActivities()
    {
        $response = $this->service->request('user/-/activities/frequent.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user favorite activities
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFavoriteActivities()
    {
        $response = $this->service->request('user/-/activities/favorite.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Log user activity
     *
     * @throws Exception
     * @param DateTime $date Activity date and time (set proper timezone, which could be fetched via getProfile)
     * @param string $activityId Activity Id (or Intensity Level Id) from activities database,
     *                                  see http://wiki.fitbit.com/display/API/API-Log-Activity
     * @param string $duration Duration millis
     * @param string $calories Manual calories to override Fitbit estimate
     * @param string $distance Distance in km/miles (as set with setMetric)
     * @param string $distanceUnit Distance unit string (see http://wiki.fitbit.com/display/API/API-Distance-Unit)
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logActivity($date, $activityId, $duration, $calories = null, $distance = null, $distanceUnit = null, $activityName = null)
    {
        $distanceUnits = array('Centimeter', 'Foot', 'Inch', 'Kilometer', 'Meter', 'Mile', 'Millimeter', 'Steps', 'Yards');

        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        $parameters['startTime'] = $date->format('H:i');
        if (isset($activityName)) {
            $parameters['activityName'] = $activityName;
            $parameters['manualCalories'] = $calories;
        } else {
            $parameters['activityId'] = $activityId;
            if (isset($calories))
                $parameters['manualCalories'] = $calories;
        }
        $parameters['durationMillis'] = $duration;
        if (isset($distance))
            $parameters['distance'] = $distance;
        if (isset($distanceUnit) && in_array($distanceUnit, $distanceUnits))
            $parameters['distanceUnit'] = $distanceUnit;

        $response = $this->service->request('user/-/activities.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Delete user activity
     *
     * @throws Exception
     * @param string $id Activity log id
     * @return bool
     */
    public function deleteActivity($id)
    {
        $response = $this->service->request('user/-/activities/'.$id.'.'.$this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


    /**
     * Add user favorite activity
     *
     * @throws Exception
     * @param string $id Activity log id
     * @return bool
     */
    public function addFavoriteActivity($id)
    {
        $response = $this->service->request('user/-/activities/log/favorite/'.$id.'.'.$this->responseFormat, "POST");
        return $this->parseResponse($response);
    }


    /**
     * Delete user favorite activity
     *
     * @throws Exception
     * @param string $id Activity log id
     * @return bool
     */
    public function deleteFavoriteActivity($id)
    {
        $response = $this->service->request('user/-/activities/log/favorite/'.$id.'.'.$this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


    /**
     * Get full description of specific activity
     *
     * @throws Exception
     * @param  string $id Activity log Id
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getActivity($id)
    {
        $response = $this->service->request('activities/'.$id.'.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get a tree of all valid Fitbit public activities as well as private custom activities the user createds
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function browseActivities()
    {
        $response = $this->service->request('activities.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user foods for specific date
     *
     * @throws Exception
     * @param  DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFoods($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        $response = $this->service->request('user/'.$this->userId.'/foods/log/date/'.$dateStr.".".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user recent foods
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getRecentFoods()
    {
        $response = $this->service->request('user/-/foods/log/recent.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user frequent foods
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFrequentFoods()
    {
        $response = $this->service->request('user/-/foods/log/frequent.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user favorite foods
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFavoriteFoods()
    {
        $response = $this->service->request('user/-/foods/log/favorite.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Log user food
     *
     * @throws Exception
     * @param DateTime $date Food log date
     * @param string $foodId Food Id from foods database (see searchFoods)
     * @param string $mealTypeId Meal Type Id from foods database (see searchFoods)
     * @param string $unitId Unit Id, should be allowed for this food (see getFoodUnits and searchFoods)
     * @param string $amount Amount in specified units
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logFood($date, $foodId, $mealTypeId, $unitId, $amount, $foodName = null, $calories = null, $brandName = null, $nutrition = null)
    {
        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        if (isset($foodName)) {
            $parameters['foodName'] = $foodName;
            $parameters['calories'] = $calories;
            if (isset($brandName))
                $parameters['brandName'] = $brandName;
            if (isset($nutrition)) {
                foreach ($nutrition as $i => $value) {
                    $parameters[$i] = $nutrition[$i];
                }
            }
        } else {
            $parameters['foodId'] = $foodId;
        }
        $parameters['mealTypeId'] = $mealTypeId;
        $parameters['unitId'] = $unitId;
        $parameters['amount'] = $amount;

        $response = $this->service->request('user/-/foods/log.'.$this->responseFormat, "POST");
        return $this->parseResponse($response);
    }


    /**
     * Delete user food
     *
     * @throws Exception
     * @param string $id Food log id
     * @return bool
     */
    public function deleteFood($id)
    {
        $response = $this->service->request('user/-/foods/log/'.$id.'.'.$this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


    /**
     * Add user favorite food
     *
     * @throws Exception
     * @param string $id Food log id
     * @return bool
     */
    public function addFavoriteFood($id)
    {
        $response = $this->service->request('user/-/foods/log/favorite/'.$id.'.'.$this->responseFormat, "POST");
        return $this->parseResponse($response);
    }


    /**
     * Delete user favorite food
     *
     * @throws Exception
     * @param string $id Food log id
     * @return bool
     */
    public function deleteFavoriteFood($id)
    {
        $response = $this->service->request('user/-/foods/log/favorite/'.$id.'.'.$this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


    /**
     * Get user meal sets
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getMeals()
    {
        $response = $this->service->request('user/-/meals.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get food units library
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFoodUnits()
    {
        $response = $this->service->request('foods/units.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Search for foods in foods database
     *
     * @throws Exception
     * @param string $query Search query
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function searchFoods($query)
    {
        $response = $this->service->request('foods/search.'.$this->responseFormat. "?query=" . rawurlencode($query));
        return $this->parseResponse($response);
    }


    /**
     * Get description of specific food from food db (or private for the user)
     *
     * @throws Exception
     * @param  string $id Food Id
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFood($id)
    {
        $response = $this->service->request('foods/'.$id.'.'.$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Create private foods for a user
     *
     * @throws Exception
     * @param string $name Food name
     * @param string $defaultFoodMeasurementUnitId Unit id of the default measurement unit
     * @param string $defaultServingSize Default serving size in measurement units
     * @param string $calories Calories in default serving
     * @param string $description
     * @param string $formType ("LIQUID" or "DRY)
     * @param string $nutrition Array of nutritional values, see http://wiki.fitbit.com/display/API/API-Create-Food
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function createFood($name, $defaultFoodMeasurementUnitId, $defaultServingSize, $calories, $description = null, $formType = null, $nutrition = null)
    {
        $parameters = array();
        $parameters['name'] = $name;
        $parameters['defaultFoodMeasurementUnitId'] = $defaultFoodMeasurementUnitId;
        $parameters['defaultServingSize'] = $defaultServingSize;
        $parameters['calories'] = $calories;
        if (isset($description))
            $parameters['description'] = $description;
        if (isset($formType))
            $parameters['formType'] = $formType;
        if (isset($nutrition)) {
            foreach ($nutrition as $i => $value) {
                $parameters[$i] = $nutrition[$i];
            }
        }

        $response = $this->service->request('foods.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Get user water log entries for specific date
     *
     * @throws Exception
     * @param  DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getWater($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        $response = $this->service->request("user/-/foods/log/water/date/".$dateStr.".".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Log user water
     *
     * @throws Exception
     * @param DateTime $date Log entry date (set proper timezone, which could be fetched via getProfile)
     * @param string $amount Amount in ml/fl oz (as set with setMetric) or waterUnit
     * @param string $waterUnit Water Unit ("ml", "fl oz" or "cup")
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logWater($date, $amount, $waterUnit = null)
    {
        $waterUnits = array('ml', 'fl oz', 'cup');

        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        $parameters['amount'] = $amount;
        if (isset($waterUnit) && in_array($waterUnit, $waterUnits))
            $parameters['unit'] = $waterUnit;

        $response = $this->service->request('user/-/foods/log/water.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
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
        $response = $this->service->request('user/-/foods/log/water/'.$id.'.'.$this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


    /**
     * Get user sleep log entries for specific date
     *
     * @throws Exception
     * @param  DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getSleep($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        $response = $this->service->request("user/" . $this->userId . "/sleep/date/".$dateStr.".".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Log user sleep
     *
     * @throws Exception
     * @param DateTime $date Sleep date and time (set proper timezone, which could be fetched via getProfile)
     * @param string $duration Duration millis
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logSleep($date, $duration)
    {
        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        $parameters['startTime'] = $date->format('H:i');
        $parameters['duration'] = $duration;

        $response = $this->service->request('user/-/sleep.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
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
        $response = $this->service->request('user/-/sleep/'.$id.'.'.$this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


    /**
     * Get user body measurements
     *
     * @throws Exception
     * @param  DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getBody($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        $response = $this->service->request("user/" . $this->userId . "/body/date/".$dateStr.".".$this->responseFormat);
        return $this->parseResponse($response);
    }

    /**
     * Log user body measurements
     *
     * @throws Exception
     * @param string $weight Float number. For en_GB units, provide floating number of stones (i.e. 11 st. 4 lbs = 11.2857143)
     * @param string $fat Float number
     * @param string $bicep Float number
     * @param string $calf Float number
     * @param string $chest Float number
     * @param string $forearm Float number
     * @param string $hips Float number
     * @param string $neck Float number
     * @param string $thigh Float number
     * @param string $waist Float number
     * @param DateTime $date Date Log entry date (set proper timezone, which could be fetched via getProfile)
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */

    public function logBody($date, $weight = null, $fat = null, $bicep = null, $calf = null, $chest = null, $forearm = null, $hips = null, $neck = null, $thigh = null, $waist = null)
    {
        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');

        if (isset($weight))
            $parameters['weight'] = $weight;
        if (isset($fat))
            $parameters['fat'] = $fat;
        if (isset($bicep))
            $parameters['bicep'] = $bicep;
        if (isset($calf))
            $parameters['calf'] = $calf;
        if (isset($chest))
            $parameters['chest'] = $chest;
        if (isset($forearm))
            $parameters['forearm'] = $forearm;
        if (isset($hips))
            $parameters['hips'] = $hips;
        if (isset($neck))
            $parameters['neck'] = $neck;
        if (isset($thigh))
            $parameters['thigh'] = $thigh;
        if (isset($waist))
            $parameters['waist'] = $waist;

        $response = $this->service->request('user/-/body.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Log user weight
     *
     * @throws Exception
     * @param string $weight Float number. For en_GB units, provide floating number of stones (i.e. 11 st. 4 lbs = 11.2857143)
     * @param DateTime $date If present, log entry date, now by default (set proper timezone, which could be fetched via getProfile)
     * @return bool
     */
    public function logWeight($weight, $date = null)
    {
        $parameters = array();
        $parameters['weight'] = $weight;
        if (isset($date))
            $parameters['date'] = $date->format('Y-m-d');

        $response = $this->service->request('user/-/body/weight.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Get user blood pressure log entries for specific date
     *
     * @throws Exception
     * @param  DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getBloodPressure($date, $dateStr)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        $response = $this->service->request("user/-/bp/date/".$dateStr.".".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Log user blood pressure
     *
     * @throws Exception
     * @param DateTime $date Log entry date (set proper timezone, which could be fetched via getProfile)
     * @param string $systolic Systolic measurement
     * @param string $diastolic Diastolic measurement
     * @param DateTime $time Time of the measurement (set proper timezone, which could be fetched via getProfile)
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logBloodPressure($date, $systolic, $diastolic, $time = null)
    {
        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        $parameters['systolic'] = $systolic;
        $parameters['diastolic'] = $diastolic;
        if (isset($time))
            $parameters['time'] = $time->format('H:i');

        $response = $this->service->request('user/-/bp.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Delete user blood pressure record
     *
     * @throws Exception
     * @param string $id Blood pressure log id
     * @return bool
     */
    public function deleteBloodPressure($id)
    {
        $response = $this->service->request('user/-/bp/'.$id.'.'.$this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


    /**
     * Get user glucose log entries for specific date
     *
     * @throws Exception
     * @param  DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getGlucose($date, $dateStr)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        $response = $this->service->request("user/-/glucose/date/".$dateStr.".".$this->responseFormat);
        return $this->parseResponse($response);
    }

    /**
     * Log user glucose and HbA1c
     *
     * @throws Exception
     * @param DateTime $date Log entry date (set proper timezone, which could be fetched via getProfile)
     * @param string $tracker Name of the glucose tracker
     * @param string $glucose Glucose measurement
     * @param string $hba1c Glucose measurement
     * @param DateTime $time Time of the measurement (set proper timezone, which could be fetched via getProfile)
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logGlucose($date, $tracker, $glucose, $hba1c = null, $time = null)
    {
        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        $parameters['tracker'] = $tracker;
        $parameters['glucose'] = $glucose;
        if (isset($hba1c))
            $parameters['hba1c'] = $hba1c;
        if (isset($time))
            $parameters['time'] = $time->format('H:i');

        $response = $this->service->request('user/-/glucose.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Get user heart rate log entries for specific date
     *
     * @throws Exception
     * @param  DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getHeartRate($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        $response = $this->service->request("user/-/heart/date/".$dateStr.".".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Log user heart rate
     *
     * @throws Exception
     * @param DateTime $date Log entry date (set proper timezone, which could be fetched via getProfile)
     * @param string $tracker Name of the glucose tracker
     * @param string $heartRate Heart rate measurement
     * @param DateTime $time Time of the measurement (set proper timezone, which could be fetched via getProfile)
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logHeartRate($date, $tracker, $heartRate, $time = null)
    {
        $parameters = array();
        $parameters['date'] = $date->format('Y-m-d');
        $parameters['tracker'] = $tracker;
        $parameters['heartRate'] = $heartRate;
        if (isset($time))
            $parameters['time'] = $time->format('H:i');

        $response = $this->service->request('user/-/heart.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Delete user heart rate record
     *
     * @throws Exception
     * @param string $id Heart rate log id
     * @return bool
     */
    public function deleteHeartRate($id)
    {
        $response = $this->service->request('user/-/heart/'.$id.'.'.$this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


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
     * @param  $basedate DateTime or 'today', to_period
     * @param  $to_period DateTime or '1d, 7d, 30d, 1w, 1m, 3m, 6m, 1y, max'
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

        $response = $this->service->request("user/" . $this->userId . $path . '/date/' . (is_string($basedate) ? $basedate : $basedate->format('Y-m-d')) . "/" . (is_string($to_period) ? $to_period : $to_period->format('Y-m-d')) . '.' . $this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Launch IntradayTimeSeries requests
     *
     * Allowed types are:
     *            'caloriesOut', 'steps', 'floors', 'elevation'
     *
     * @throws Exception
     * @param string $type
     * @param  $date DateTime or 'today'
     * @param  $start_time DateTime
     * @param  $end_time DateTime
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

        $response = $this->service->request("user/-" . $path . "/date/" . (is_string($date) ? $date : $date->format('Y-m-d')) . "/1d" . ((!empty($start_time) && !empty($end_time)) ? "/time/" . $start_time->format('H:i') . "/" . $end_time->format('H:i') : "") . '.' . $this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user's activity statistics (lifetime statistics from the tracker device and total numbers including the manual activity log entries)
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getActivityStats()
    {
        $response = $this->service->request("user/" . $this->userId . "/activities.".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get list of devices and their properties
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getDevices()
    {
        $response = $this->service->request("user/-/devices.".$this->responseFormat);
        return $this->parseResponse($response);
    }

    /**
     * Get user friends
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFriends()
    {
        $response = $this->service->request("user/" . $this->userId . "/friends.".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get user's friends leaderboard
     *
     * @throws Exception
     * @param string $period Depth ('7d' or '30d')
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFriendsLeaderboard($period = '7d')
    {
        $response = $this->service->request("user/-/friends/leaders/" . $period . ".".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Invite user to become friends
     *
     * @throws Exception
     * @param string $userId Invite user by id
     * @param string $email Invite user by email address (could be already Fitbit member or not)
     * @return bool
     */
    public function inviteFriend($userId = null, $email = null)
    {
        $parameters = array();
        if (isset($userId))
            $parameters['invitedUserId'] = $userId;
        if (isset($email))
            $parameters['invitedUserEmail'] = $email;

        $response = $this->service->request('user/-/friends/invitations.'.$this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Accept invite to become friends from user
     *
     * @throws Exception
     * @param string $userId Id of the inviting user
     * @return bool
     */
    public function acceptFriend($userId)
    {
        $parameters = array();
        $parameters['accept'] = 'true';

        $response = $this->service->request("user/-/friends/invitations/" . $userId . "." . $this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Accept invite to become friends from user
     *
     * @throws Exception
     * @param string $userId Id of the inviting user
     * @return bool
     */
    public function rejectFriend($userId)
    {
        $parameters = array();
        $parameters['accept'] = 'false';

        $response = $this->service->request("user/-/friends/invitations/" . $userId . "." . $this->responseFormat, "POST", $parameters);
        return $this->parseResponse($response);
    }


    /**
     * Add subscription
     *
     * @throws Exception
     * @param string $id Subscription Id
     * @param string $path Subscription resource path (beginning with slash). Omit to subscribe to all user updates.
     * @return
     */
    public function addSubscription($id, $path = null, $subscriberId = null)
    {
        $userHeaders = array();
        if ($subscriberId)
            $userHeaders['X-Fitbit-Subscriber-Id'] = $subscriberId;


        if (isset($path))
            $path = '/' . $path;
        else
            $path = '';

        $response = $this->service->request("user/-" . $path . "/apiSubscriptions/" . $id . "." . $this->responseFormat, "POST", $parameters, $userHeaders);
        return $this->parseResponse($response);
    }


    /**
     * Delete user subscription
     *
     * @throws Exception
     * @param string $id Subscription Id
     * @param string $path Subscription resource path (beginning with slash)
     * @return bool
     */
    public function deleteSubscription($id, $path = null)
    {
        if (isset($path))
            $path = '/' . $path;
        else
            $path = '';

        $response = $this->service->request("user/-" . $path . "/apiSubscriptions/" . $id . "." . $this->responseFormat, "DELETE");
        return $this->parseResponse($response);
    }


    /**
     * Get list of user's subscriptions for this application
     *
     * @throws Exception
     * @return
     */
    public function getSubscriptions()
    {
        $response = $this->service->request("user/-/apiSubscriptions.".$this->responseFormat);
        return $this->parseResponse($response);
    }


    /**
     * Get CLIENT+VIEWER and CLIENT rate limiting quota status
     *
     * @throws Exception
     * @return RateLimiting
     */
    public function getRateLimit()
    {
        $clientAndUser = $this->parseResponse($this->service->request("account/clientAndViewerRateLimitStatus.".$this->responseFormat));
        $client = $this->parseResponse($this->service->request("account/clientRateLimitStatus.".$this->responseFormat));

        return new RateLimiting(
            $clientAndUser->rateLimitStatus->remainingHits,
            $client->rateLimitStatus->remainingHits,
            $clientAndUser->rateLimitStatus->resetTime,
            $client->rateLimitStatus->resetTime,
            $clientAndUser->rateLimitStatus->hourlyLimit,
            $client->rateLimitStatus->hourlyLimit
        );
    }


    /**
     * Make custom call to any API endpoint
     *
     * @param string $url Endpoint url after '.../1/'
     * @param array $parameters Request parameters
     * @param string $method (OAUTH_HTTP_METHOD_GET, OAUTH_HTTP_METHOD_POST, OAUTH_HTTP_METHOD_PUT, OAUTH_HTTP_METHOD_DELETE)
     * @param array $userHeaders Additional custom headers
     * @return Response
     */
    public function customCall($url, $parameters, $method, $userHeaders = array())
    {
        $response = $this->service->request($url, $method, $parameters, $userHeaders);
        return $this->parseResponse($response);
    }

    /**
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    private function parseResponse($response)
    {
        if ($this->responseFormat == 'xml')
            return simplexml_load_string($response);
        else if ($this->responseFormat == 'json')
            return json_decode($response);

        return $response;
    }
}