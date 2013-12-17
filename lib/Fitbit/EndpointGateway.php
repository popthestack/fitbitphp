<?php

namespace Fitbit;

class EndpointGateway {

    /**
     * @var \OAuth\OAuth1\Service\ServiceInterface
     */
    protected $service;

    /**
     * @var string
     */
    protected $responseFormat;

    /**
     * @var string
     */
    protected $userID;

    /**
     * Set Fitbit service
     *
     * @access public
     * @param \OAuth\OAuth1\Service\ServiceInterface
     * @return \Fitbit\EndpointGateway
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Set response format.
     * 
     * @access public
     * @param string $response_format
     * @return \Fitbit\EndpointGateway
     */
    public function setResponseFormat($format)
    {
        $this->responseFormat = $format;
        return $this;
    }

    /**
     * Set Fitbit user ids.
     *
     * @access public
     * @param string $id
     * @return \Fitbit\EndpointGateway
     */
    public function setUserID($id)
    {
        $this->userID = $id;
        return $this;
    }

    /**
     * Make an API request
     *
     * @access protected
     * @param string $resource Endpoint after '.../1/'
     * @param string $method ('GET', 'POST', 'PUT', 'DELETE')
     * @param array $body Request parameters
     * @param array $extraHeaders Additional custom headers
     * @return mixed stdClass for json response, SimpleXMLElement for XML response.
     */
    protected function makeApiRequest($resource, $method = 'GET', $body = array(), $extraHeaders = array())
    {
        $path = $resource . '.' . $this->responseFormat;

        if ($method == 'GET' && $body) {
            $path .= '?' . http_build_query($body);
            $body = array();
        }

        $response = $this->service->request($path, $method, $body, $extraHeaders);

        return $this->parseResponse($response);
    }

    /**
     * Parse json or XML response.
     *
     * @access private
     * @return mixed stdClass for json response, SimpleXMLElement for XML response.
     */
    private function parseResponse($response)
    {
        if ($this->responseFormat == 'json') {
            return json_decode($response);
        } elseif ($this->responseFormat == 'xml') {
            return simplexml_load_string($response);
        }

        return $response;
    }

    /**
     * Get user foods for specific date
     *
     * @throws Exception
     * @param  \DateTime|string $baseDate
     * @param  string $period
     * @param  \DateTime|string $endDate
     * @return mixed SimpleXMLElement or the value encoded in json as an object
     */
    public function getSeries($path, $baseDate = null, $period = null, $endDate = null)
    {
        $baseDate = $baseDate ?: 'today';
        $end = ($period) ? $period : ($endDate) ?: '1d';

        if ($baseDate instanceof Datetime)
            $baseDate = $baseDate->format("Y-m-d");

        if ($end instanceof Datetime) 
            $end = $end->format("Y-m-d");

        $endpoint = sprintf('user/%s/%s/%s/%s', $this->userID, $path, $baseDate, $end);
        return $this->makeApiRequest($endpoint);
    }

    /**
     * Get CLIENT+VIEWER and CLIENT rate limiting quota status
     *
     * @access public
     * @return \Fitbit\RateLimiting
     */
    public function getRateLimit()
    {
        $clientAndUser = $this->makeApiRequest('account/clientAndViewerRateLimitStatus');
        $client        = $this->makeApiRequest('account/clientRateLimitStatus');

        return new RateLimiting(
            $clientAndUser->rateLimitStatus->remainingHits,
            $client->rateLimitStatus->remainingHits,
            $clientAndUser->rateLimitStatus->resetTime,
            $client->rateLimitStatus->resetTime,
            $clientAndUser->rateLimitStatus->hourlyLimit,
            $client->rateLimitStatus->hourlyLimit
        );
    }
}
