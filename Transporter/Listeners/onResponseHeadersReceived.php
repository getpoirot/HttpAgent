<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Psr\Http\Message\RequestInterface;

use Poirot\Events\Listener\aListener;

use Poirot\Stream\Streamable;

use Poirot\HttpAgent\Transporter\TransporterHttpSocket;


class onResponseHeadersReceived extends aListener
{
    /**
     * @param RequestInterface      $request
     * @param array                 $headers
     * @param TransporterHttpSocket $transporter
     *
     * $headers:
     * array['version'=>string, 'status'=>int, 'reason'=>string, 'headers'=>array(key=>val)]
     * 
     * @return mixed
     */
    function __invoke($request = null, &$headers = null, $transporter = null)
    {
        $statusCode = $headers['status'];
        
        # Handle 100 and 101 responses
        if ($statusCode == 100 || $statusCode == 101)
            ## receive data will continue after events
            // TODO
            VOID;

        # HEAD requests and 204 or 304 stat codes are not expected to have a body
        if ($statusCode == 304 || $statusCode == 204 || $request->getMethod() == 'HEAD')
            ## do not continue with body
            return array('continue' => false);
    }
}