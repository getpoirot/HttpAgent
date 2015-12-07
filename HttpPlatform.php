<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiMethod;
use Poirot\ApiClient\Interfaces\Response\iResponse;

class HttpPlatform implements iPlatform
{
    /**
     * Prepare Connection To Make Call
     *
     * - validate connection
     * - manipulate header or something in connection
     * - get connect to resource
     *
     * @param iConnection $connection
     *
     * @throws \Exception
     * @return void
     */
    function prepareConnection(iConnection $connection)
    {
        // TODO: Implement prepareConnection() method.
    }

    /**
     * Build Platform Specific Expression To Send
     * Trough Connection
     *
     * @param iApiMethod $method Method Interface
     *
     * @return mixed
     */
    function makeExpression(iApiMethod $method)
    {
        // TODO: Implement makeExpression() method.
    }

    /**
     * Build Response Object From Server Result
     *
     * - Result must be compatible with platform
     * - Throw exceptions if response has error
     *
     * @param mixed $result Server Result
     *
     * @throws \Exception
     * @return iResponse
     */
    function makeResponse($result)
    {
        // TODO: Implement makeResponse() method.
    }
}
