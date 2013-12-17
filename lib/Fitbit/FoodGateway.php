<?php

namespace Fitbit;

class FoodGateway extends EndpointGateway {

    /**
     * Get user foods for specific date
     *
     * @throws Exception
     * @param  \DateTime $date
     * @param  String $dateStr
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFoods($date, $dateStr = null)
    {
        if (!isset($dateStr)) {
            $dateStr = $date->format('Y-m-d');
        }

        return $this->makeApiRequest('user/' . $this->userID . '/foods/log/date/' . $dateStr);
    }

    /**
     * Get user recent foods
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getRecentFoods()
    {
        return $this->makeApiRequest('user/-/foods/log/recent');
    }

    /**
     * Get user frequent foods
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFrequentFoods()
    {
        return $this->makeApiRequest('user/-/foods/log/frequent');
    }


    /**
     * Get user favorite foods
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFavoriteFoods()
    {
        return $this->makeApiRequest('user/-/foods/log/favorite');
    }

    /**
     * Log user food
     *
     * @throws Exception
     * @param \DateTime $date Food log date
     * @param string $foodId Food Id from foods database (see searchFoods)
     * @param string $mealTypeId Meal Type Id from foods database (see searchFoods)
     * @param string $unitId Unit Id, should be allowed for this food (see getFoodUnits and searchFoods)
     * @param string $amount Amount in specified units
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function logFood(\DateTime $date, $foodId, $mealTypeId, $unitId, $amount, $foodName = null, $calories = null, $brandName = null, $nutrition = null)
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

        return $this->makeApiRequest('user/-/foods/log', 'POST');
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
        return $this->makeApiRequest('user/-/foods/log/' . $id, 'DELETE');
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
        return $this->makeApiRequest('user/-/foods/log/favorite/', 'POST');
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
        return $this->makeApiRequest('user/-/foods/log/favorite/', 'DELETE');
    }

    /**
     * Get user meal sets
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getMeals()
    {
        return $this->makeApiRequest('user/-/meals');
    }

    /**
     * Get food units library
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFoodUnits()
    {
        return $this->makeApiRequest('foods/units');
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
        return $this->makeApiRequest('foods/search', 'GET', array('query' => $query));
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
        return $this->makeApiRequest('foods/' . $id);
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

        return $this->makeApiRequest('foods', 'POST', $parameters);
    }
}
