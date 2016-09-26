<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Connection\Http\StreamFilter\DechunkFilter;

use Poirot\Events\Listener\aListener;

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
     * @param array                 $parsed_response
     * @param iStreamable           $response
     * @param RequestInterface      $request
     * @param TransporterHttpSocket $transporter
     *
     * @return iStreamable|null
     */
    function __invoke($parsed_response = null, $response = null, $request = null, $transporter = null)
    {
        // Decoding Data:

        if (!$transporter->optsData()->isAllowDecoding())
            ## do not decode body using raw data
            return;

        $headers = $parsed_response['headers'];
        foreach ($headers as $key => $val) {
            switch (strtolower($key)) {
                // (!) Consider Alphabetic Sort If Order Is Mandatory
                case 'content-encoding':
                    $response = $this->_handleContentEncoding($val, $response);
                    break;
                case 'transfer-encoding':
                    $response = $this->_handleTransferEncoding($val, $response);
                    break;
            }
        }

        // return manipulated response
        return array('response' => $response);
    }

    /**
     * @param string      $encoding
     * @param iStreamable $response
     *
     * @return iStreamable
     */
    private function _handleContentEncoding($encoding, $response)
    {
        if (strstr(strtolower($encoding), 'gzip') === false)
            // Nothing To Do!
            return $response;

        ## Uses PHP's zlib.inflate filter to inflate deflate or gzipped content
        $stream  = new Streamable\SAggregateStreams();

        $headers = \Poirot\Connection\Http\readAndSkipHeaders($response);
        $stream->addStream(new Streamable\STemporary($headers));

        ### skip the first 10 bytes for zlib
        // limit body stream from after headers to end
        $body    = new Streamable\SLimitSegment($response, -1, $response->getCurrOffset() + 10);
        $body->resource()->appendFilter(new FilterStreamPhpBuiltin('zlib.inflate'), STREAM_FILTER_READ);

        // TODO complicated; with zlib.inflate stream just read once then all rewind reads are returns empty string
        // So write again to temporary buffer
        $temp = new Streamable\STemporary();
        $body->pipeTo($temp);

        $stream->addStream($temp);

        return $stream;
    }

    /**
     * @param string      $encoding
     * @param iStreamable $response
     *
     * @return iStreamable
     */
    private function _handleTransferEncoding($encoding, $response)
    {
        if (strstr(strtolower($encoding), 'chunked') === false)
            // Nothing To Do!
            return $response;

        $stream  = new Streamable\SAggregateStreams();

        $headers = \Poirot\Connection\Http\readAndSkipHeaders($response);
        $stream->addStream(new Streamable\STemporary($headers));

        ### skip the first 10 bytes for zlib
        // limit body stream from after headers to end
        $body    = new Streamable\SLimitSegment($response, -1, $response->getCurrOffset());
        $body->resource()->appendFilter(DechunkFilter::newInstance(), STREAM_FILTER_READ);

        // So write again to temporary buffer
        $temp = new Streamable\STemporary();
        $body->pipeTo($temp);

        $stream->addStream($temp);

        return $stream;
    }
}
