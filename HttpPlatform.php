<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiMethod;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\Http\Message\HttpRequest;
use Poirot\Http\Message\HttpResponse;
use Poirot\HttpAgent\Interfaces\iHttpTransporter;
use Poirot\HttpAgent\Transporter\StreamHttpTransporter;

class HttpPlatform implements iPlatform
{
    /** @var Browser */
    protected $browser;

    /**
     * Construct
     *
     * @param $browser
     */
    function __construct(Browser $browser)
    {
       $this->browser = $browser;
    }

    /**
     * Prepare Connection To Make Call
     *
     * - validate connection
     * - manipulate header or something in connection
     * - get connect to resource
     *
     * @param StreamHttpTransporter|iConnection $connection
     *
     * @throws \Exception
     * @return StreamHttpTransporter|iHttpTransporter
     */
    function prepareConnection(iConnection $connection)
    {
        $brwOptions = $this->browser->inOptions();

        $reConnect = false;

        # check if we have something changed in connection options
        if ($conOptions = $brwOptions->getConnection())
            foreach($conOptions->props()->readable as $prop) {
                if (
                    ## not has new option or it may changed
                    !$connection->inOptions()->__isset($prop)
                    || ($connection->inOptions()->__get($prop) !== $conOptions->__get($prop))
                ) {
                    $connection->inOptions()->__set($prop, $conOptions->__get($prop));
                    $reConnect = true;
                }
            }

        # base url as connection server_url option
        $baseUrl = $this->browser->inOptions()->getBaseUrl();
        if ($baseUrl->toString() !== $connection->inOptions()->getServerUrl()) {
            $connection->inOptions()->setServerUrl($baseUrl);
            $reConnect = true;
        }


        ## disconnect old connection to reconnect with newly options if has
        if ($connection->isConnected() && $reConnect)
            $connection->close();

        return $connection;
    }

    /**
     * Build Platform Specific Expression To Send
     * Trough Connection
     *
     * @param iApiMethod $method Method Interface
     *
     * @return HttpRequest
     */
    function makeExpression(iApiMethod $method)
    {
        $request = (new HttpRequest(['method' => 'GET', 'host' => 'raya-media.com']));

        return $request;
    }

    /**
     * Build Response Object From Server Result
     *
     * - Result must be compatible with platform
     * - Throw exceptions if response has error
     *
     * @param HttpResponse $result Server Result
     *
     * @throws \Exception
     * @return iResponse
     */
    function makeResponse($result)
    {
        $result->flush();
        die();
    }
}
