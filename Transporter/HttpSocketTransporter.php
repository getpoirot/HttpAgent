<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\ApiClient\AbstractTransporter;
use Poirot\ApiClient\Exception\ApiCallException;
use Poirot\ApiClient\Transporter\HttpSocketConnection;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Message\HttpRequest;
use Poirot\Http\Message\HttpResponse;
use Poirot\Http\Psr\Interfaces\RequestInterface;
use Poirot\HttpAgent\Interfaces\iHttpTransporter;
use Poirot\HttpAgent\Transporter\Listeners\onEventsCloseConnection;
use Poirot\HttpAgent\Transporter\Listeners\onRequestPrepareSend;
use Poirot\HttpAgent\Transporter\Listeners\onResponseBodyReceived;
use Poirot\HttpAgent\Transporter\Listeners\onResponseHeadersReceived;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Streamable;
use Poirot\Stream\StreamClient;

/*
$request = (new HttpRequest(['method' => 'GET', 'host' => 'raya-media.com', 'headers' => [
    'Accept' => '* /*',
    'User-Agent' => 'Poirot/Client HTTP',
    'Accept-Encoding' => 'gzip, deflate, sdch',
]]))->toString();

$stream = new HttpSocketTransporter(
    HttpSocketTransporter::optionsIns()
        ->setServerUrl('http://www.raya-media.com/')
        ->setTimeout(30)
        ->setPersistent(true)
);

$response = $stream->send($request);
kd($response->toString());
*/

// TODO Abstract HttpAgent Transporter

class HttpSocketTransporter extends HttpSocketConnection
    implements iHttpTransporter
{
    /** @var Streamable When Connected */
    protected $streamable;
    /** @var  */
    protected $connected_options;

    /** @var bool  */
    protected $isRequestComplete = false;

    /**
     * Write Received Server Data To It Until Complete
     * @var Streamable\TemporaryStream */
    protected $_buffer;
    protected $_buffer_seek = 0; # current buffer write position
    /** @var HttpResponse */
    protected $_completed_response;

    /** @var TransporterHttpEvents */
    protected $event;


    /**
     * Construct
     *
     * - pass connection options on construct
     *
     * @param array|iDataSetConveyor $options Connection Options
     */
    function __construct($options = null)
    {
        parent::__construct($options);
        $this->__attachDefaultListeners();
    }

    /**
     * Before Send Prepare Expression
     * @param mixed $expr
     * @return iStreamable|string
     */
    function onBeforeSendPrepareExpression($expr)
    {
        if ($expr instanceof RequestInterface || is_string($expr))
            ## convert PSR request to Poirot
            $expr = new HttpRequest($expr);

        if (!$expr instanceof iHttpRequest)
            throw new \InvalidArgumentException(sprintf(
                'Request Expression must be string, iHttpRequest or RequestInterface. given: (%s).'
                , \Poirot\Core\flatten($expr)
            ));


        // ...

        ## handle prepare request headers event
        $httpRequest = $expr;
        $emitter = $this->event()->trigger(TransporterHttpEvents::EVENT_REQUEST_SEND_PREPARE, [
            'request'     => $httpRequest,
            'transporter' => $this,
        ]);
        /** @var iHttpRequest $expr */
        $expr = $httpRequest = $emitter->collector()->getRequest();

        if ($httpRequest instanceof iHttpRequest)
        {
            # ! # support for large body streams

            /** @var Streamable\AggregateStream $expr */
            $expr = new Streamable\AggregateStream;

            $expr->addStream(
                (new Streamable\TemporaryStream(
                    $httpRequest->renderRequestLine()
                    . $httpRequest->renderHeaders()
                ))->rewind()
            );

            $body = $httpRequest->getBody();
            if ($body !== null || $body !== '') {
                if (!$body instanceof iStreamable)
                    $body = new Streamable\TemporaryStream($body);

                $expr->addStream($body->rewind());
            }
        }

        return $expr;
    }

    /**
     * $responseHeaders can be changed by reference
     *
     * @param string $responseHeaders
     *
     * @return boolean consider continue with reading body from stream?
     */
    function onResponseHeaderReceived(&$responseHeaders)
    {
        $responseHeaders = new HttpResponse($responseHeaders);
        $emitter  = $this->event()->trigger(TransporterHttpEvents::EVENT_RESPONSE_HEADERS_RECEIVED, [
            'response'    => $responseHeaders,
            'transporter' => $this,
            'request'     => $this->getRequest(),
        ]);

        ## consider terminate receive body
        return $emitter->collector()->getContinue();
    }

    /**
     * Get Body And Response Headers And Return Expected Response
     *
     * @param string|mixed     $responseHeaders default has headers string but it can changed
     *                                          with onResponseHeaderReceived
     * @param iStreamable|null $body
     *
     * @return mixed Expected Response
     */
    function onResponseReceivedComplete($responseHeaders, $body)
    {
        /** @var HttpResponse $responseHeaders */

        $emitter = $this->event()->trigger(TransporterHttpEvents::EVENT_RESPONSE_BODY_RECEIVED, [
            'response'    => $responseHeaders,
            'transporter' => $this,

            'body'        => $body,

            'request'     => $this->getRequest(),
            'continue'    => false, ## no more request by default
        ]);

        $bodyStream = $emitter->collector()->getBody();
        $responseHeaders->setBody($bodyStream);

        return $responseHeaders;
    }

    /**
     * Is Request Complete
     *
     * - return false if not request was sent
     * - also return true if response available
     *
     * @return bool
     */
    function isRequestComplete()
    {
        return $this->isRequestComplete;
    }


    // ...

    /**
     * Get Events
     *
     * @return TransporterHttpEvents
     */
    function event()
    {
        if (!$this->event)
            $this->event = new TransporterHttpEvents;

        return $this->event;
    }

    /**
     * @override just for ide completion
     * @return HttpTransporterOptions
     */
    function inOptions()
    {
        return parent::inOptions();
    }

    /**
     * @override
     * @return HttpTransporterOptions
     */
    static function newOptions($builder = null)
    {
        return new HttpTransporterOptions($builder);
    }


    // ... TODO some move outside

    protected function __attachDefaultListeners()
    {
        $this->event()->on(
            TransporterHttpEvents::EVENT_REQUEST_SEND_PREPARE
            , new onRequestPrepareSend
            , 100
        );

        // ...

        $this->event()->on(
            TransporterHttpEvents::EVENT_RESPONSE_HEADERS_RECEIVED
            , new onResponseHeadersReceived
            , 100
        );

        $this->event()->on(
            TransporterHttpEvents::EVENT_RESPONSE_BODY_RECEIVED
            , new onResponseBodyReceived
            , 100
        );

        $this->event()->on([
                TransporterHttpEvents::EVENT_RESPONSE_HEADERS_RECEIVED,
                TransporterHttpEvents::EVENT_RESPONSE_BODY_RECEIVED,
            ]
            , new onEventsCloseConnection
            , -1000
        );
    }
}
