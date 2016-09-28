<?php
namespace Poirot\HttpAgent\Interfaces\Browser;

use Psr\Http\Message\RequestInterface;


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
     * @param RequestInterface $request
     *
     * @return RequestInterface|null Copy/Clone
     */
    function withHttpRequest(RequestInterface $request);
}
