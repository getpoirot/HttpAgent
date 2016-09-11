<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\Connection\Exception\ApiCallException;
use Poirot\Connection\Exception\ConnectException;
use Poirot\Connection\Interfaces\iConnection;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\HttpAgent\Transporter\HttpTransporterOptions;
use Poirot\HttpAgent\Transporter\TransporterHttpEvents;
use Psr\Http\Message\RequestInterface;

interface iHttpTransporter
    extends iConnection
    , iEventProvider
{
    /**
     * Send Expression To Server
     *
     * - send expression to server through transporter
     *   resource
     *
     * - don't set request globally through request() if
     *   expr set
     *
     * !! it must be connected
     *
     * @param iHttpRequest|RequestInterface|string $expr Expression
     *
     * @throws ApiCallException|ConnectException
     * @return HttpResponse Prepared Server Response
     */
    function send($expr = null);

    /**
     * Set Request Expression To Send Over Wire
     *
     * @param iHttpRequest|RequestInterface|string $expr
     *
     * @return $this
     */
    function request($expr);

    /**
     * Get Latest Request
     *
     * @return null|iHttpRequest|RequestInterface|string
     */
    function getRequest();

    /**
     * Is Request Complete
     *
     * - return false if not request was sent
     * - also return true if response available
     *
     * @return bool
     */
    function isRequestComplete();

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
