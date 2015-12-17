<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\AbstractListener;
use Poirot\Http\Message\HttpResponse;
use Poirot\HttpAgent\Transporter\StreamHttpTransporter;

class onEventsCloseConnection extends AbstractListener
{
    /**
     * @param StreamHttpTransporter $transporter
     * @param HttpResponse          $response
     *
     * @return mixed
     */
    function __invoke($transporter = null, $response = null)
    {
        ## Close the connection if requested to do so by the server
        $headers = $response->getHeaders();
        if (
            $headers->has('connection')
            && strstr($headers->get('connection')->renderValueLine(), 'close') !== false
            && $transporter->isConnected()
        ) {
            $transporter->close();

            return ['continue' => false];
        }
    }
}
