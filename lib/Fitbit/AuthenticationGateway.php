<?php

namespace Fitbit;

class AuthenticationGateway extends EndpointGateway {

    public function isAuthorized()
    {
        return $this->service->getStorage()->hasAccessToken('FitBit');
    }

    /**
     * Initiate the login process
     *
     * @access public
     * @return void
     */
    public function initiateLogin()
    {
        $token = $this->service->requestRequestToken();
        $url = $this->service->getAuthorizationUri(['oauth_token' => $token->getRequestToken()]);
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Authenticate user, request access token.
     *
     * @access public
     * @param string $token
     * @param string $verifier
     * @return \OAuth\Common\Token\TokenInterface\TokenInterface
     */
    public function authenticateUser($token, $verifier)
    {
        $tokenSecret = $this->service->getStorage()->retrieveAccessToken('FitBit');
        
        return $this->service->requestAccessToken(
            $token,
            $verifier,
            $tokenSecret->getRequestTokenSecret()
        );
    }

    /**
     * Reset session
     *
     * @access public
     * @return void
     */
    public function resetSession()
    {
        // TODO: Need to add clear to the interface for phpoauthlib
        $this->service->getStorage()->clearToken();
    }

    protected function verifyToken()
    {
        if (!$this->isAuthorized()) {
            throw new \Exception("You must be authorized to make requests");
        }
    }
}
