<?php
/**
 * Basic response wrapper for customCall
 *
 */

namespace Fitbit;

class Response
{
    public $response;
    public $code;

    /**
     * @param  $response string
     * @param  $code string
     */
    public function __construct($response, $code)
    {
        $this->response = $response;
        $this->code = $code;
    }
}