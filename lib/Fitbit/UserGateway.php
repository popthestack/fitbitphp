<?php

namespace Fitbit;

class UserGateway extends EndpointGateway {

    /**
     * API wrappers
     *
     */
    public function getProfile()
    {
        return $this->makeApiRequest('user/' . $this->userID . '/profile');
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

        return $this->makeApiRequest('user/' . $this->userID . '/profile', 'POST', $parameters);
    }

    /**
     * Get list of devices and their properties
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getDevices()
    {
        return $this->makeApiRequest('user/-/devices');
    }

    /**
     * Get user friends
     *
     * @throws Exception
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getFriends()
    {
        return $this->makeApiRequest('user/' . $this->userID . '/friends');
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
        return $this->makeApiRequest('user/-/friends/leaders/' . $period);
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

        return $this->makeApiRequest('user/-/friends/invitations', 'POST', $parameters);
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

        return $this->makeApiRequest('user/-/friends/invitations/' . $userId, 'POST', $parameters);
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

        return $this->makeApiRequest('user/-/friends/invitations/' . $userId, 'POST', $parameters);
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
        $extraHeaders = array();
        if ($subscriberId) {
            $extraHeaders['X-Fitbit-Subscriber-Id'] = $subscriberId;
        }

        if (isset($path)) {
            $path = '/' . $path;
        } else {
            $path = '';
        }

        return $this->makeApiRequest('user/-' . $path . '/apiSubscriptions/' . $id, 'POST', $parameters, $extraHeaders);
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
        if (isset($path)) {
            $path = '/' . $path;
        } else {
            $path = '';
        }

        return $this->makeApiRequest('user/-' . $path . '/apiSubscriptions/' . $id, 'DELETE');
    }

    /**
     * Get list of user's subscriptions for this application
     *
     * @throws Exception
     * @return
     */
    public function getSubscriptions()
    {
        return $this->makeApiRequest('user/-/apiSubscriptions');
    }
}
