<?php
namespace Poirot\HttpAgent\Browser\Plugin;

use Poirot\Http\Header\HeaderFactory;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\HttpAgent\Interfaces\iBrowserExpressionPlugin;

class BFormDataPlugin extends AbstractBrowserPlugin
    implements iBrowserExpressionPlugin

{
    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     */
    function withHttpRequest(iHttpRequest $request)
    {
        $params = \Poirot\Std\iterator_to_array($this);
        $body   = http_build_query($params, null, '&');

        $request->setBody($body);
        $request->getHeaders()->set(HeaderFactory::factory('Content-Type', 'application/x-www-form-urlencoded'));
    }
}
