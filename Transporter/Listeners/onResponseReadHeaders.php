<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\AbstractListener;
use Poirot\Http\Message\HttpResponse;
use Poirot\HttpAgent\Transporter\StreamHttpTransporter;
use Poirot\Stream\Streamable;

class onResponseReadHeaders extends AbstractListener
{
    /**
     * @param StreamHttpTransporter $transporter
     * @param HttpResponse          $response
     * @param Streamable            $stream
     *
     * @return mixed
     */
    function __invoke($transporter = null, $response = null, $stream = null)
    {
        // Handle 100 and 101 responses internally by restarting the read again
        if ($statusCode == 100 || $statusCode == 101) return $this->read();
    }
}