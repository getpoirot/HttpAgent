<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\aListener;

use Poirot\HttpAgent\Transporter\TransporterHttpSocket;
use Poirot\Stream\Interfaces\iStreamable;
use Psr\Http\Message\RequestInterface;


class onResponseReceivedCloseConnection 
    extends aListener
{
    /**
     * // TODO it must happen by request header
     * 
     * Close Connection when Response Received by Response Header 
     * 
     * @param array                 $parsed_response
     * @param iStreamable           $response
     * @param RequestInterface      $request
     * @param TransporterHttpSocket $transporter
     * 
     * @return iStreamable|null
     */
    function __invoke($parsed_response = null, $response = null, $request = null, $transporter = null)
    {
        if (!$transporter->isConnected())
            // Nothing To Do!!
            return;
        
        ## Close the connection if requested to do so by the server
        $headers = $parsed_response['headers'];
        foreach ($headers as $key => $val) {
            if (strtolower($key) != 'connection')
                continue;
            
            if (strtolower($val) == 'close')
                $transporter->close();
            
            break;
        }
    }
}
