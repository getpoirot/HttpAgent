<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Connection\Http\ConnectionHttpSocket;

use Poirot\Http\HttpResponse;

use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Http\Psr\RequestBridgeInPsr;
use Poirot\HttpAgent\Interfaces\iTransporterHttp;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Streamable;

use Poirot\HttpAgent\Transporter\Listeners\onEventsCloseConnection;
use Poirot\HttpAgent\Transporter\Listeners\onRequestPrepareSend;
use Poirot\HttpAgent\Transporter\Listeners\onResponseBodyReceived;
use Poirot\HttpAgent\Transporter\Listeners\onResponseHeadersReceived;


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

class TransporterHttpSocket 
    extends ConnectionHttpSocket
    implements iTransporterHttp
{
    /** @var Streamable When Connected */
    protected $streamable;
    /** @var  */
    protected $connected_options;

    /** @var bool  */
    protected $isRequestComplete = false;

    /**
     * Write Received Server Data To It Until Complete
     * @var Streamable\STemporary */
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
     * @param array|\Traversable $options Connection Options
     */
    function __construct($options = null)
    {
        parent::__construct($options);
        $this->__attachDefaultListeners();
    }

    /**
     * @override IDE Completion
     *
     * @param iHttpRequest $expr
     *
     * @return $this
     */
    function request($expr)
    {
        if (!$expr instanceof iHttpRequest)
            throw new \InvalidArgumentException(sprintf(
                'Expression must instance of iHttpRequest; given: (%s).'
                , \Poirot\Std\flatten($expr)
            ));

        $this->expr = $expr;
        return $this;
    }
    
    /**
     * Before Send Prepare Expression
     * 
     * @param mixed $expr
     * 
     * @return iStreamable
     */
    function triggerBeforeSendPrepareExpression($expr)
    {
        ## handle prepare request headers event
        $httpRequest = clone $expr;
        $this->event()->trigger(TransporterHttpEvents::EVENT_REQUEST_PREPARE_EXPRESSION, array(
            'request'     => $httpRequest,
            'transporter' => $this,
        ));
        
        /** @var iHttpRequest $expr */
        $expr = $httpRequest;
        $expr = new RequestBridgeInPsr($expr);
        return parent::triggerBeforeSendPrepareExpression($expr);
    }

    /**
     * $responseHeaders can be changed by reference
     *
     * @param string $responseHeaders
     *
     * @return boolean consider continue with reading body from stream?
     */
    function triggerResponseHeaderReceived(&$responseHeaders)
    {
        $responseHeaders = new HttpResponse($responseHeaders);
        $emitter  = $this->event()->trigger(TransporterHttpEvents::EVENT_RESPONSE_HEADERS_RECEIVED, array(
            'response'    => &$responseHeaders,
            'transporter' => $this,
            'request'     => $this->getLastRequest(),
        ));

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

        $emitter = $this->event()->trigger(TransporterHttpEvents::EVENT_RESPONSE_BODY_RECEIVED, array(
            'response'    => $responseHeaders,
            'transporter' => $this,

            'body'        => $body,

            'request'     => $this->getLastRequest(),
            'continue'    => false, ## no more request by default
        ));

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
     * @return TransporterHttpOptions
     */
    function optsData()
    {
        return parent::optsData();
    }

    /**
     * @override
     * @return TransporterHttpOptions
     */
    static function newOptsData($builder = null)
    {
        # provide "server_address" connection options from "base_url" browser option:
        // made absolute server url from given baseUrl, but keep original untouched
        // http://raya-media/path/to/uri --> http://raya-media/
        $baseUrl = $this->optsData()->getBaseUrl();
        if (false !== $baseUrl = parse_url($baseUrl))
        {
            if ( isset($baseUrl['scheme']) && isset($baseUrl['host']) ) {
                // Connect To HOST
                $serverHost = '';
                (!isset($baseUrl['scheme'])) ?: $serverHost .= $baseUrl['scheme'].'://';
                $serverHost .= $baseUrl['host'];
                (!isset($baseUrl['port']))   ?: $serverHost .= ':'.$baseUrl['port'];

                if ($serverHost !== $transporter->optsData()->getServerAddress()) {
                    $transporter->optsData()->setServerAddress($serverHost);
                    $reConnect = true;
                }
            }
        }
        
        return new TransporterHttpOptions($builder);
    }


    // ... TODO some move outside

    protected function __attachDefaultListeners()
    {
        $this->event()->on(
            TransporterHttpEvents::EVENT_REQUEST_PREPARE_EXPRESSION
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
