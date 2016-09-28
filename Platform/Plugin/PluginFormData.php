<?php
namespace Poirot\HttpAgent\Platform\Plugin;

use Poirot\Http\HttpMessage\Request\StreamBodyMultiPart;
use Poirot\HttpAgent\Interfaces\Browser\iPluginBrowserExpression;
use Poirot\Stream\Psr\StreamBridgeInPsr;
use Psr\Http\Message\RequestInterface;


class PluginFormData
    extends BaseBrowserPlugin
    implements iPluginBrowserExpression
{
    const SERVICE_NAME = 'form-data';

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

        $boundary = '---WebKitFormBoundary'.uniqid();
        $stream   = new StreamBodyMultiPart($params, $boundary);
        $stream   = new StreamBridgeInPsr($stream->rewind());

        $request = $request
            ->withBody($stream)
            ->withHeader('Content-Type', 'multipart/form-data; boundary='.$boundary)
        ;
        
        return $request;
    }
}
