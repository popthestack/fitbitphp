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

    <?php
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

If you want to retrieve the OAuth token and secret from the session to store elsewhere (e.g. a database):

    <?php
    $storage = $factory->getStorageAdapter();
    $token   = $storage->retrieveAccessToken('FitBit');
    
    // Save these somewhere:
    $oauth_token  = $token->getRequestToken();
    $oauth_secret = $token->getRequestTokenSecret();

Here's how to use your OAuth token and secret without the `Session` storage adapter.
It's a little cumbersome, but it works. If I ever have time for it, I'd like to
replace the current OAuth library with something that doesn't enforce so much... stuff.

    <?php
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

## Notes ##

 * By default, all requests assume you want data for the authorized user (viewer). There are, however, several endpoints you can use to access the data of other Fitbit users, given that you have permission to access their data. This is accomplished by setting the Fitbit User ID with the `setUserID` method available on `ApiGatewayFactory` and the Endpoint Gateways (e.g. `UserGateway`, `FoodGateway`).
 * *Subscriptions*: this library has some basic methods to add/delete subscriptions, but it's your responsibility to track the list and maintain server endpoints to receive notifications from Fitbit, as well as register them at [http://dev.fitbit.com](http://dev.fitbit.com). See [Subscriptions-API](http://wiki.fitbit.com/display/API/Subscriptions-API) for more information.
