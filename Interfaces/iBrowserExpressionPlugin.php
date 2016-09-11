<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\Http\Interfaces\iHttpRequest;

/**
 * Plugins implementing this get expression request object
 * before send to server, actually request can be manipulated
 * and get back to platform.
 */

interface iBrowserExpressionPlugin
{
    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     */
    function withHttpRequest(iHttpRequest $request);
}
