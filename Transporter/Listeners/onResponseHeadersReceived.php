<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\AbstractListener;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Message\HttpRequest;
use Poirot\Http\Message\HttpResponse;
use Poirot\HttpAgent\Transporter\StreamHttpTransporter;
use Poirot\Stream\Streamable;

class onResponseHeadersReceived extends AbstractListener
{
    /**
     * @param StreamHttpTransporter $transporter
     * @param HttpResponse          $response
     * @param Streamable            $stream
     * @param iHttpRequest          $request
     *
     * @return mixed
     */
    function __invoke($transporter = null, $response = null, $stream = null, $request = null)
    {
        $statusCode = $response->getStatCode();

        # Handle 100 and 101 responses
        if ($statusCode == 100 || $statusCode == 101)
            ## receive data will continue after events
            $transporter->reset();

        # HEAD requests and 204 or 304 stat codes are not expected to have a body
        if ($statusCode == 304 || $statusCode == 204 || $request->getMethod() == HttpRequest::METHOD_HEAD)
            ## do not continue with body
            return ['continue' => false];
    }
}