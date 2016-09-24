<?php
namespace Poirot\HttpAgent\Browser\Plugin;

use Psr\Http\Message\RequestInterface;

use Poirot\HttpAgent\Interfaces\iPluginBrowserExpression;


class BrowserPluginJsonData
    extends BaseBrowserPlugin
    implements iPluginBrowserExpression
{
    /**
     * Manipulate Http Request
     *
     * @param RequestInterface $request
     * 
     * @return null|RequestInterface
     */
    function withHttpRequest(RequestInterface $request)
    {
        $params  = \Poirot\Std\cast($this)->toArray();

        $body    = json_encode($params);
        $request = $request
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json')
        ;

        return $request;
    }
}
