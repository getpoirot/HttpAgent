<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\aListener;

use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;

use Poirot\Stream\Streamable;

use Poirot\HttpAgent\Transporter\TransporterHttpSocket;


class onResponseHeadersReceived extends aListener
{
    /**
     * @param TransporterHttpSocket $transporter
     * @param HttpResponse          $response
     * @param iHttpRequest          $request
     *
     * @return mixed
     */
    function __invoke($transporter = null, $response = null, $request = null)
    {
        $statusCode = $response->getStatusCode();

        # Handle 100 and 101 responses
        if ($statusCode == 100 || $statusCode == 101)
            ## receive data will continue after events
            // TODO
            $transporter->getConnect();

        # HEAD requests and 204 or 304 stat codes are not expected to have a body
        if ($statusCode == 304 || $statusCode == 204 || $request->getMethod() == HttpRequest::METHOD_HEAD)
            ## do not continue with body
            return array('continue' => false);
    }
}