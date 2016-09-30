<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\aListener;

use Poirot\HttpAgent\Transporter\TransporterHttpSocket;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;


class onRequestPrepareExpression 
    extends aListener
{
    /**
     * ...
     *
     * @param TransporterHttpSocket &$transporter by reference
     * @param RequestInterface      &$request     by reference
     *
     * @return RequestInterface|null
     */
    function __invoke($request = null, $transporter = null)
    {
        if (!$request instanceof RequestInterface)
            // Nothing to do
            return;
        
        # Header Content-Length:

        /**
         * Http Messages With Body Should be with Content-Length
         * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.4
         * @see https://issues.apache.org/jira/browse/TS-2902
         */
        $body = $request->getBody();

        $length = false;
        if ($body) {
            if ($body instanceof StreamInterface)
                $length = $body->getSize();
            else
                $length = strlen($body);
            
            
        }

        if ($length !== false)
            $request = $request->withHeader('Content-Length', (string) $length);

        return array('request' => $request);
    }
}
