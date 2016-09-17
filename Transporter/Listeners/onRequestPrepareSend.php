<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\aListener;

use Poirot\Http\Header\FactoryHttpHeader;
use Poirot\Http\HttpRequest;
use Poirot\Http\Interfaces\iHttpRequest;

use Poirot\Stream\Interfaces\iStreamable;

use Poirot\HttpAgent\Transporter\TransporterHttpSocket;


class onRequestPrepareSend 
    extends aListener
{
    /**
     * @param TransporterHttpSocket $transporter
     * @param iHttpRequest          $request
     *
     * @return mixed
     */
    function __invoke($transporter = null, $request = null)
    {
        if (!$request instanceof HttpRequest)
            // Nothing to do
            return;
        
        # Header Content-Length:

        /**
         * Http Messages With Body Should be with Content-Length
         * !! without this post requests always not working
         * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.4
         * @see https://issues.apache.org/jira/browse/TS-2902
         */
        $body = $request->getBody();

        $length = false;
        if ($body) {
            if ($body instanceof iStreamable)
                $length = $body->getSize();
            else
                $length = strlen($body);
        }

        if ($length !== false) {
            if (!$request->headers()->has('Content-Length'))
                $request->headers()->insert(FactoryHttpHeader::of( array('Content-Length' => (string) $length) ));
        }
    }
}
