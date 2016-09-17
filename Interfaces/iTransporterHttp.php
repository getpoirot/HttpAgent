<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\Connection\Exception\ApiCallException;
use Poirot\Connection\Interfaces\iConnection;

use Poirot\Events\Interfaces\Respec\iEventProvider;

use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Psr\Http\Message\RequestInterface;

use Poirot\HttpAgent\Transporter\TransporterHttpEvents;


interface iTransporterHttp
    extends iConnection
    , iEventProvider
{
    // Note: Interfaces Override for IDE Completion
    
    
    // Implement iConnection:
    
    /**
     * Send Expression To Server
     *
     * - send expression to server through transporter
     *   resource
     *
     * - don't set request globally through request() if
     *   expr set
     *
     * !! getConnect IF NOT
     *
     * @param iHttpRequest|RequestInterface|string $expr Expression
     *
     * @throws ApiCallException
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
    function getLastRequest();

    
    // Implement iTransporterHttp
    
    /**
     * Is Request Complete
     *
     * - return false if not request was sent
     * - also return true if response available
     *
     * @return bool
     */
    function isRequestComplete();

    
    // Implement iEventProvider

    /**
     * Get Events
     *
     * @return TransporterHttpEvents
     */
    function event();
}
