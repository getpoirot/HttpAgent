<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\ApiClient\Exception\ApiCallException;
use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Message\HttpResponse;
use Poirot\Http\Psr\Interfaces\RequestInterface;
use Poirot\HttpAgent\Transporter\HttpTransporterOptions;
use Poirot\HttpAgent\Transporter\TransporterHttpEvents;

interface iHttpTransporter
    extends iConnection
    , iEventProvider
{
    /**
     * Send Expression To Server
     *
     * - send expression to server through connection
     *   resource
     * - get connect if connection not stablished yet
     *
     * @param iHttpRequest|RequestInterface|string $expr Expression
     *
     * @throws ApiCallException
     * @return HttpResponse Prepared Server Response
     */
    function send($expr);

    /**
     * Is Request Complete
     *
     * - return false if not request was sent
     * - also return true if response available
     *
     * @return bool
     */
    function isRequestComplete();

    /**
     * Reset Response Data
     *
     * - clear current data gathering from server response
     *
     * @return $this
     */
    function reset();

    // ...

    /**
     * Get Events
     *
     * @return TransporterHttpEvents
     */
    function event();

    // ...

    /**
     * @override just for ide completion
     * @return HttpTransporterOptions
     */
    function inOptions();

    /**
     * @override
     * @return HttpTransporterOptions
     */
    static function newOptions();
}
