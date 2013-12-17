## FitbitPHP ##

Basic wrapper for the OAuth-based [FitBit](http://fitbit.com) [REST API](http://dev.fitbit.com). See [dev.fitbit.com](http://dev.fitbit.com) for details on their OAuth implementation.

Both this library and the Fitbit API are in **beta**.

This library does not require the PHP OAuth extension. It should work on any server with PHP >= 5.3.

## Installation ##
This package is installable with composer:
    "popthestack/fitbit": "dev-master"

## Usage ##

You need a consumer key and secret. You can obtain them by registering an application at [http://dev.fitbit.com](http://dev.fitbit.com).

Simple, but full OAuth workflow example:

```php
$factory = new \Fitbit\ApiGatewayFactory;
$factory->setCallbackURL($callback_url);
$factory->setCredentials($consumer_key, $consumer_secret);

$adapter = new \OAuth\Common\Storage\Session();
$factory->setStorageAdapter($adapter);

$auth_gateway = $factory->getAuthenticationGateway();

if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) {
    $auth_gateway->authenticateUser($_GET['oauth_token'], $_GET['oauth_verifier']);
} elseif (isset($_GET['connect'])) {
    $auth_gateway->initiateLogin();
}

if ($auth_gateway->isAuthorized()) {
    $user_gateway = $factory->getUserGateway();
    $user_profile = $user_gateway->getProfile();
    echo '<pre>';
    print_r($user_profile);
    echo '</pre>';
} else {
    echo 'Not connected.';
}
```

If you want to retrieve the OAuth token and secret from the session to store elsewhere (e.g. a database):

```php
$storage = $factory->getStorageAdapter();
$token   = $storage->retrieveAccessToken('FitBit');

// Save these somewhere:
$oauth_token  = $token->getRequestToken();
$oauth_secret = $token->getRequestTokenSecret();
```

Here's how to use your OAuth token and secret without the `Session` storage adapter.
It's a little cumbersome, but it works. If I ever have time for it, I'd like to
replace the current OAuth library with something that doesn't enforce so much... stuff.

```php
$token = new \OAuth\OAuth1\Token\StdOAuth1Token();
$token->setRequestToken($oauth_token);
$token->setRequestTokenSecret($oauth_secret);
$token->setAccessToken($oauth_token);
$token->setAccessTokenSecret($oauth_secret);

$adapter = new \OAuth\Common\Storage\Memory();
$adapter->storeAccessToken('FitBit', $token);

$factory->setStorageAdapter($adapter);

$user_gateway = $factory->getUserGateway();
$food_gateway = $factory->getFoodGateway();

$user_profile = $user_gateway->getProfile();
$user_devices = $user_gateway->getDevices();
$foods        = $food_gateway->searchFoods('banana split');

echo '<pre>';
print_r($user_profile);
print_r($user_devices);
print_r($foods);
echo '</pre>';
```

## Time Series Data ##

Time series data can be colected as described [http://wiki.fitbit.com/display/API/API-Get-Time-Series](http://wiki.fitbit.com/display/API/API-Get-Time-Series)

The base class, TimeSeriesGateway, uses the magic __call method to map methods calls to resource endpoints. The method name is converted to the last segment of the URI, the rest of the path is handled by the class.

```php
$sleep_time_series_gateway = $factory->getTimeSeriesGateway();
$minutes_asleep = $sleep_time_series_gateway->getMinutesAsleep();
```

A method call without parameters will default to a time series of today/1d. A period, baseDate and endDate can be passed to specify the time series. *Note: period will override endDate.

```php
//past seven days
$minutes_asleep = $sleep_time_series_gateway->getMinutesAsleep('today','7d');

//seven days before the new year (*Note you can also pass Datetime objects for baseDate and endDate)
$minutes_asleep = $sleep_time_series_gateway->getMinutesAsleep('2014-01-01','7d'); 

//first week of new year
$minutes_asleep = $sleep_time_series_gateway->getMinutesAsleep('2014-01-01', null, '2014-01-07'); 
```

The Activities Time Series Resource allows you to limit the data collected to that logged only by the tracker. To do so pass true|false (default false) as the first argument in activites timeseries calls

```php
$activities_time_series_gateway = $factory->getActivitiesSeriesGateway();
//get the minutes of very active activity from the tracker only for the previous 7 days
$tracker_minutes_very_active = $activities_time_series_gateway->getMinutesVeryActive(true, 'today', '7d');
```
*Note: the activities/caloriesBMR resource does not appear to have a corresponding activities/tracker/caloriesBMR. The tracker parameter for getCaloriesBMR(true) will automatically be set to false. 


## Notes ##

 * By default, all requests assume you want data for the authorized user (viewer). There are, however, several endpoints you can use to access the data of other Fitbit users, given that you have permission to access their data. This is accomplished by setting the Fitbit User ID with the `setUserID` method available on `ApiGatewayFactory` and the Endpoint Gateways (e.g. `UserGateway`, `FoodGateway`).
 * *Subscriptions*: this library has some basic methods to add/delete subscriptions, but it's your responsibility to track the list and maintain server endpoints to receive notifications from Fitbit, as well as register them at [http://dev.fitbit.com](http://dev.fitbit.com). See [Subscriptions API](https://wiki.fitbit.com/display/API/Fitbit+Subscriptions+API) for more information.

 ## Known Issues ##

At the time of writing, the following resources do not currently work, or are documented incorrectly by Fitbit's documentation.

* activities/floors
* activities/elevation
