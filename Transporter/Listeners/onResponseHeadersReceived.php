<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\aListener;
use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\HttpAgent\Transporter\HttpSocketTransporter;
use Poirot\Stream\Streamable;


class onResponseHeadersReceived extends aListener
{
    /**
     * @param HttpSocketTransporter $transporter
     * @param HttpResponse          $response
     * @param Streamable            $stream
     * @param iHttpRequest          $request
     *
     * @return mixed
     */
    function __invoke($transporter = null, $response = null, $stream = null, $request = null)
    {
        $statusCode = $response->getStatusCode();

        # Handle 100 and 101 responses
        if ($statusCode == 100 || $statusCode == 101)
            ## receive data will continue after events
            $transporter->reset();

        # HEAD requests and 204 or 304 stat codes are not expected to have a body
        if ($statusCode == 304 || $statusCode == 204 || $request->getMethod() == HttpRequest::METHOD_HEAD)
            ## do not continue with body
            return array('continue' => false);

        /*$statusPlugin = new Status(['message_object' => $response]);
        if (!$statusPlugin->isSuccess())
            ## always connection will closed, no need to continue
            return ['continue' => false];*/
    }
}