<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\Http\Interfaces\iHttpRequest;


/**
 * Plugins implementing this get expression request object
 * before send to server, actually request can be manipulated
 * and get back to platform.
 */
interface iPluginBrowserExpression
    extends iPluginBrowser
{
    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     *
     * @return iHttpRequest Copy Clone
     */
    function withHttpRequest(iHttpRequest $request);
}
