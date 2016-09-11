<?php
namespace Poirot\HttpAgent\Browser\Plugin;

use Poirot\Http\Header\FactoryHttpHeader;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\HttpAgent\Interfaces\iBrowserExpressionPlugin;

class BrowserPluginFormData 
    extends BaseBrowserPlugin
    implements iBrowserExpressionPlugin

{
    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     */
    function withHttpRequest(iHttpRequest $request)
    {
        $params = \Poirot\Std\cast($this)->toArray();
        $body   = http_build_query($params, null, '&');

        $request->setBody($body);
        $request->headers()->insert(
            FactoryHttpHeader::of( array('Content-Type' => 'application/x-www-form-urlencoded') )
        );
    }
}
