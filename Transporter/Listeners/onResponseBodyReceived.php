<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Connection\Http\StreamFilter\DechunkFilter;
use Poirot\Events\Listener\aListener;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\HttpAgent\Transporter\HttpSocketTransporter;
use Poirot\Stream\Filter\FilterStreamPhpBuiltin;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Streamable;

class onResponseBodyReceived 
    extends aListener
{
    /**
     * @param HttpSocketTransporter $transporter
     * @param iStreamable           $body
     * @param HttpResponse          $response
     * @param Streamable            $stream
     * @param iHttpRequest          $request
     *
     * @return mixed
     */
    function __invoke($transporter = null, $body = null, $response = null, $stream = null, $request = null)
    {
        $headers = $response->headers();

        // Decoding Data:

        if (!$body || !$transporter->optsData()->isAllowDecoding())
            ## do not decode body using raw data
            return array('body' => $body);

        if ($headers->has('Content-Encoding')
            && strstr(strtolower($headers->get('Content-Encoding')->current()->renderValueLine()), 'gzip') !== false
        ) {
            ## Uses PHP's zlib.inflate filter to inflate deflate or gzipped content

            $body->resource()->appendFilter(new FilterStreamPhpBuiltin('zlib.inflate'), STREAM_FILTER_READ);
            ### skip the first 10 bytes for zlib
            $body = new Streamable\SLimitSegment($body, -1, 10);
        }

        if ($headers->has('transfer-encoding')
            && strstr(strtolower($headers->get('Transfer-Encoding')->current()->renderValueLine()), 'chunked') !== false
        ) {
            $body->getResource()->appendFilter(new DechunkFilter, STREAM_FILTER_READ);

        }

        return array('body' => $body);
    }
}
