<?php
namespace Poirot\HttpAgent\Platform\Plugin;

use Poirot\HttpAgent\Interfaces\Browser\iPluginBrowserExpression;
use Poirot\Stream\Psr\StreamBridgeInPsr;
use Poirot\Stream\Streamable\STemporary;
use Psr\Http\Message\RequestInterface;



class PluginJsonData
    extends BaseBrowserPlugin
    implements iPluginBrowserExpression
{
    const SERVICE_NAME = 'json-data';
    
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
        $stream  = new STemporary($body);
        $stream  = new StreamBridgeInPsr($stream->rewind());
        
        $request = $request
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/json')
        ;

        return $request;
    }
}
