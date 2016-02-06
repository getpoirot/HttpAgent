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

// TODO extend from ApiClient\HttpSocketConnection
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
     * Send Request To Server
     *
     * @param string|iHttpRequest|RequestInterface $expr
     * @return HttpResponse
     * @throws \Exception
     */
    protected function doHandleRequest($expr)
    {
        if ($expr instanceof RequestInterface)
            ## convert PSR request to Poirot
            $expr = new HttpRequest($expr);

        if ($expr instanceof iHttpRequest)
            $expr = $expr->toString();


        return parent::doHandleRequest($expr);
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

    /**
     * Reset Response Data
     *
     * - clear current data gathering from server response
     *
     * @return $this
     */
    function reset()
    {
        if ($this->_buffer)
            $this->_buffer->getResource()->close();

        $this->_buffer = null;
        return $this;
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
        $this->onEvent(self::EVENT_REQUEST_SEND_PREPARE, function($httpRequest) {
            $emitter = $this->event()->trigger(TransporterHttpEvents::EVENT_REQUEST_SEND_PREPARE, [
                'request'     => $httpRequest,
                'transporter' => $this,
            ]);

            /** @var iHttpRequest $expr */
            return $httpRequest = $emitter->collector()->getRequest();
        });

        $this->onEvent(self::EVENT_RESPONSE_RECEIVED_HEADER, function($responseHeaders, $requestStd, $continue) {
            $response = new HttpResponse($responseHeaders);
            $request  = new HttpRequest($requestStd->headers);
            $emitter  = $this->event()->trigger(TransporterHttpEvents::EVENT_RESPONSE_HEADERS_RECEIVED, [
                'response'    => $response,
                'transporter' => $this,
                'request'     => $request,
            ]);

            ## consider terminate receive body
            $continue->isDone = !$emitter->collector()->getContinue();
            return $response;
        });

        $this->onEvent(self::EVENT_RESPONSE_RECEIVED_COMPLETE, function($responseHeaders, $body, $requestStd) {
            $request  = new HttpRequest($requestStd->headers);
            $emitter = $this->event()->trigger(TransporterHttpEvents::EVENT_RESPONSE_BODY_RECEIVED, [
                'response'    => $responseHeaders,
                'transporter' => $this,

                'body'        => $body,

                'request'     => $request,
                'continue'    => false, ## no more request by default
            ]);

            $bodyStream = $emitter->collector()->getBody();
            $responseHeaders->setBody($bodyStream);

            return $responseHeaders;
        });


        // ...

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
