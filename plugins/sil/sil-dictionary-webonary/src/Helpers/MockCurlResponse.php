<?php

namespace SIL\Webonary\Helpers;

use SIL\Webonary\Interfaces\ICurlResponse;

class MockCurlResponse
{
    public string $Method;

    /** @var ICurlResponse */
    public mixed $Response;

    /**
     * @param string $method
     * @param ICurlResponse $response
     */
    public function __construct(string $method, mixed $response)
    {
        $this->Method = $method;
        $this->Response = $response;
    }
}
