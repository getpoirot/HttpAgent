<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\Http\Interfaces\iHttpResponse;


interface iPluginBrowserResponse
    extends iPluginBrowser
{
    /**
     * Manipulate Http Response
     *
     * @param iHttpResponse $response
     * 
     * @return iHttpResponse Copy/Clone
     */
    function withHttpResponse(iHttpResponse $response);
}
