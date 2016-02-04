<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\ApiClient\Exception\ApiCallException;
use Poirot\ApiClient\Exception\ConnectException;
use Poirot\ApiClient\Interfaces\iTransporter;
use Poirot\Core\Interfaces\iOptionsProvider;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Message\HttpResponse;
use Poirot\Http\Psr\Interfaces\RequestInterface;
use Poirot\HttpAgent\Transporter\HttpTransporterOptions;
use Poirot\HttpAgent\Transporter\TransporterHttpEvents;

interface iHttpTransporter
    extends iTransporter
    , iEventProvider
{
    /**
     * Send Expression To Server
     *
     * - send expression to server through connection
     *   resource
     *
     * !! it must be connected
     *
     * @param iHttpRequest|RequestInterface|string $expr Expression
     *
     * @throws ApiCallException|ConnectException
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
    // function inOptions();

    /**
     * @override
     * @return HttpTransporterOptions
     */
    // static function newOptions($builder = null);
}
