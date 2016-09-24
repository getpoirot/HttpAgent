<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Connection\Http\ConnectionHttpSocket;

use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Streamable;

use Poirot\HttpAgent\Interfaces\iTransporterHttp;
use Poirot\HttpAgent\Transporter\Listeners\onResponseReceivedCloseConnection;
use Poirot\HttpAgent\Transporter\Listeners\onRequestPrepareExpression;
use Poirot\HttpAgent\Transporter\Listeners\onResponseReceived;
use Poirot\HttpAgent\Transporter\Listeners\onResponseHeadersReceived;
use Psr\Http\Message\RequestInterface;


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

/**
 * Extended ConnectionHttpSocket To Add More Control Over
 * Http Protocol.
 * 
 */
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
    /** @var  */
    protected $_completed_response;

    /** @var EventHeapTransporterHttp */
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
        
        $this->_attachDefaultListeners();
    }

    /**
     * @override Just Get RequestInterface 
     * 
     * @param RequestInterface $expr
     * 
     * @return $this
     */
    function request($expr)
    {
        if (!$expr instanceof RequestInterface)
            throw new \InvalidArgumentException(sprintf(
                'Expression must instance of RequestInterface PSR; given: (%s).'
                , \Poirot\Std\flatten($expr)
            ));

        return parent::request($expr);
    }
    
    /**
     * Before Send Prepare Expression
     *
     * @param RequestInterface $expr
     *
     * @return iStreamable
     */
    function makeStreamFromRequestExpression($expr)
    {
        ## handle prepare request headers event
        $httpRequest = clone $expr;
        $this->event()->trigger(EventHeapTransporterHttp::EVENT_REQUEST_PREPARE_EXPRESSION, array(
            'request'     => $httpRequest,
            'transporter' => $this,
        ));
        
        return parent::makeStreamFromRequestExpression($expr);
    }

    /**
     * Determine received response headers
     *
     * @param array &$parsedResponse By reference
     *        array['version'=>string, 'status'=>int, 'reason'=>string, 'headers'=>array(key=>val)]
     *
     * @return true|false Consider continue with reading body from stream?
     */
    function canContinueWithReceivedHeaders(&$parsedResponse)
    {
        $result = parent::canContinueWithReceivedHeaders($parsedResponse);

        $emitter  = $this->event()->trigger(EventHeapTransporterHttp::EVENT_RESPONSE_HEADERS_RECEIVED, array(
            'parsed_response' => $parsedResponse,
            'transporter'     => $this,
            'request'         => $this->getLastRequest(),
        ));

        ## consider terminate receive body
        $result &= $emitter->collector()->isContinue();
        
        return $result;
    }

    /**
     * Finalize Response Buffer
     *
     * @param iStreamable $response
     * @param array       $parsedResponse
     *
     * @return iStreamable
     */
    function finalizeResponseFromStream($response, $parsedResponse)
    {
        $emitter = $this->event()->trigger(EventHeapTransporterHttp::EVENT_RESPONSE_RECEIVED, array(
            'parsed_response' => $parsedResponse,
            'response'        => $response,
            'request'         => $this->getLastRequest(),
            'transporter'     => $this,
        ));

        if ($r = $emitter->collector()->getResponse())
            $response = $r;

        return parent::finalizeResponseFromStream($response, $parsedResponse);
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
    
    
    // Implement EventProvider:

    /**
     * Get Events
     *
     * @return EventHeapTransporterHttp
     */
    function event()
    {
        if (!$this->event)
            $this->event = new EventHeapTransporterHttp;

        return $this->event;
    }

    
    // Implement Options Provider:
    
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
        return new TransporterHttpOptions($builder);
    }


    // ... TODO some move outside

    protected function _attachDefaultListeners()
    {
        $this->event()->on(
            EventHeapTransporterHttp::EVENT_REQUEST_PREPARE_EXPRESSION
            , new onRequestPrepareExpression
            , 100
        );

        // ...

        $this->event()->on(
            EventHeapTransporterHttp::EVENT_RESPONSE_HEADERS_RECEIVED
            , new onResponseHeadersReceived
            , 100
        );

        $this->event()->on(
            EventHeapTransporterHttp::EVENT_RESPONSE_RECEIVED
            , new onResponseReceived
            , 100
        );

        $this->event()->on(
            EventHeapTransporterHttp::EVENT_RESPONSE_RECEIVED
            , new onResponseReceivedCloseConnection
            , -1000
        );
    }
}
