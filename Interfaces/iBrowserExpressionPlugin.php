<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\Http\Interfaces\Message\iHttpRequest;

interface iBrowserExpressionPlugin
{
    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     */
    function withHttpRequest(iHttpRequest $request);
}
