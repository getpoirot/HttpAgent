<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Events\Listener\AbstractListener;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Message\HttpResponse;
use Poirot\HttpAgent\Transporter\StreamFilter\ChunkTransferDecodeFilter;
use Poirot\HttpAgent\Transporter\HttpSocketTransporter;
use Poirot\Stream\Filter\PhpRegisteredFilter;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Streamable;

class onResponseBodyReceived extends AbstractListener
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
        $headers = $response->getHeaders();


        // Decoding Data:

        if (!$transporter->inOptions()->isAllowDecoding())
            ## do not decode body using raw data
            return;


        if ($headers->has('Content-Encoding')
            && strstr(strtolower($headers->get('Content-Encoding')->renderValueLine()), 'gzip') !== false
        ) {
            ## Uses PHP's zlib.inflate filter to inflate deflate or gzipped content

            $body->getResource()->appendFilter(new PhpRegisteredFilter('zlib.inflate'), STREAM_FILTER_READ);
            ### skip the first 10 bytes for zlib
            $body = new Streamable\SegmentWrapStream($body, -1, 10);
        }

        if ($headers->has('transfer-encoding')
            && strstr(strtolower($headers->get('Transfer-Encoding')->renderValueLine()), 'chunked') !== false
        ) {
            $body->getResource()->appendFilter(new ChunkTransferDecodeFilter, STREAM_FILTER_READ);

        }

        return ['body' => $body];
    }
}
