<?php
namespace Poirot\HttpAgent\Interfaces;

use Psr\Http\Message\ResponseInterface;


interface iPluginBrowserResponse
    extends iPluginBrowser
{
    /**
     * Manipulate Http Response
     *
     * @param ResponseInterface $response
     * 
     * @return ResponseInterface|null Copy/Clone
     */
    function withHttpResponse(ResponseInterface $response);
}
