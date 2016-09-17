<?php
namespace Poirot\HttpAgent\Browser\Plugin;

use Poirot\Http\Header\FactoryHttpHeader;
use Poirot\Http\Interfaces\iHttpRequest;

use Poirot\HttpAgent\Interfaces\iPluginBrowserExpression;


class BrowserPluginFormData 
    extends BaseBrowserPlugin
    implements iPluginBrowserExpression

{
    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     *
     * @return iHttpRequest
     */
    function withHttpRequest(iHttpRequest $request)
    {
        $request = clone $request;
        $params  = \Poirot\Std\cast($this)->toArray();
        $body    = http_build_query($params, null, '&');

        $request->setBody($body);
        $request->headers()
            ->del('Content-Type')
            ->insert(FactoryHttpHeader::of(
                array('Content-Type' => 'application/x-www-form-urlencoded')
            ));

        return $request;
    }
}
