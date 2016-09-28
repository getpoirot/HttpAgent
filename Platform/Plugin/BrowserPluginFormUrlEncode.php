<?php
namespace Poirot\HttpAgent\Browser\Plugin;

use Poirot\HttpAgent\Interfaces\iPluginBrowserExpression;
use Poirot\Stream\Psr\StreamBridgeInPsr;
use Poirot\Stream\Streamable\STemporary;
use Psr\Http\Message\RequestInterface;


class BrowserPluginFormUrlEncode
    extends BaseBrowserPlugin
    implements iPluginBrowserExpression
{
    const SERVICE_NAME = 'form-urlencode-data';

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

        $stream  = new STemporary($body);
        $stream  = new StreamBridgeInPsr($stream->rewind());

        $request = $request
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ;
        
        return $request;
    }
}
