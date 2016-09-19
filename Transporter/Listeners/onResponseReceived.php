<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Connection\Http\StreamFilter\DechunkFilter;

use Poirot\Events\Listener\aListener;

use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;

use Poirot\Stream\Filter\FilterStreamPhpBuiltin;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Streamable;

use Poirot\HttpAgent\Transporter\TransporterHttpSocket;
use Psr\Http\Message\RequestInterface;


class onResponseReceived 
    extends aListener
{
    /**
     *
     * @param array                 $parsedResponse
     * @param iStreamable           $response
     * @param RequestInterface      $request
     * @param TransporterHttpSocket $transporter
     *
     * @return iStreamable|null
     */
    function __invoke($parsedResponse = null, $response = null, $request = null, $transporter = null)
    {
        // Decoding Data:

        if (!$transporter->optsData()->isAllowDecoding())
            ## do not decode body using raw data
            return;

        $headers = $parsedResponse['headers'];
        foreach ($headers as $key => $val) {
            switch (strtolower($key)) {
                // (!) Consider Alphabetic Sort If Order Is Mandatory
                case 'content-encoding':
                    $response = $this->_handleContentEncoding($response, $val);
                    break;
                case 'transfer-encoding':
                    $response = $this->_handleTransferEncoding($response, $val);
                    break;
            }
        }

        return $response;
    }

    /**
     * @param iStreamable $response
     * @param string      $encoding
     *
     * @return iStreamable
     */
    private function _handleContentEncoding($response, $encoding)
    {
        if (strstr(strtolower($encoding), 'gzip') === false)
            // Nothing To Do!
            return $response;

        ## Uses PHP's zlib.inflate filter to inflate deflate or gzipped content

        // TODO

        $body->resource()->appendFilter(new FilterStreamPhpBuiltin('zlib.inflate'), STREAM_FILTER_READ);
        ### skip the first 10 bytes for zlib
        $body = new Streamable\SLimitSegment($body, -1, 10);

        return $response;
    }

    /**
     * @param iStreamable $response
     * @param string      $encoding
     *
     * @return iStreamable
     */
    private function _handleTransferEncoding($response, $encoding)
    {
        if (strstr(strtolower($encoding), 'chunked') === false)
            // Nothing To Do!
            return $response;

        // TODO

        $body->getResource()->appendFilter(new DechunkFilter, STREAM_FILTER_READ);

        return $response;
    }
}
