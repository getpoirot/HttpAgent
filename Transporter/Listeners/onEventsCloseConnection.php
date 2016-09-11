<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\aListener;
use Poirot\Http\HttpResponse;
use Poirot\HttpAgent\Transporter\HttpSocketTransporter;


class onEventsCloseConnection 
    extends aListener
{
    /**
     * @param HttpSocketTransporter $transporter
     * @param HttpResponse          $response
     * @param null                  $continue
     *                              From Events To Tell Just Continue With Body
     *
     * @return mixed
     */
    function __invoke($transporter = null, $response = null, $continue = null)
    {
        ## Close the connection if requested to do so by the server
        $headers = $response->headers();
        if (
            $headers->has('connection')
            && strstr($headers->get('connection')->current()->renderValueLine(), 'close') !== false
            && $transporter->isConnected()
            && $continue === false
        )
            $transporter->close();
    }
}
