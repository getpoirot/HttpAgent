<?php
namespace Poirot\HttpAgent\Browser\Plugin;

use Poirot\HttpAgent\Interfaces\iPluginBrowserExpression;
use Psr\Http\Message\RequestInterface;


class BrowserPluginFormData 
    extends BaseBrowserPlugin
    implements iPluginBrowserExpression

{
    /**
     * Manipulate Http Request
     *
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    function withHttpRequest(RequestInterface $request)
    {
        $params  = \Poirot\Std\cast($this)->toArray();
        $body    = http_build_query($params, null, '&');

        $request = $request
            ->withBody($body)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ;
        
        return $request;
    }
}
