<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\ApiClient\AbstractConnection;
use Poirot\ApiClient\Exception\ApiCallException;
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
use Poirot\Stream\Streamable;
use Poirot\Stream\StreamClient;

/*
$request = (new HttpRequest(['method' => 'GET', 'host' => 'raya-media.com', 'headers' => [
    'Accept' => '* /*',
    'User-Agent' => 'Poirot/Client HTTP',
    'Accept-Encoding' => 'gzip, deflate, sdch',
]]))->toString();

$stream = new StreamHttpTransporter(
    StreamHttpTransporter::optionsIns()
        ->setServerUrl('http://www.raya-media.com/')
        ->setTimeout(30)
        ->setPersistent(true)
);

$response = $stream->send($request);
kd($response->toString());
*/

// TODO build AbstractTransporter Class

class StreamHttpTransporter extends AbstractConnection
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
     * @param Array|iDataSetConveyor $options Connection Options
     */
    function __construct($options = null)
    {
        parent::__construct($options);
        $this->__attachDefaultListeners();
    }

    /**
     * Get Prepared Resource Connection
     *
     * - prepare resource with options
     *
     * @throws \Exception
     */
    function getConnect()
    {
        if ($this->isConnected())
            ## close current connection if connected
            $this->close();


        $streamClient = new StreamClient;

        # apply options to resource
        ## options will not take an affect after connect
        $this->connected_options = clone $this->inOptions();

        ## determine protocol
        // TODO ssl connection with context bind
        if (
            !$this->inOptions()->__isset('server_url')
            || ! ($serverUrl = clone $this->inOptions()->getServerUrl())
        )
            throw new \RuntimeException('Server Url is Mandatory For Connect.');

        $serverUrl->setScheme('tcp');
        if (!$serverUrl->getPort())
            $serverUrl->setPort(80);

        $streamClient->setSocketUri($serverUrl->toString());

        ### options
        $streamClient->setPersistent($this->inOptions()->getPersist());
        $streamClient->setTimeout($this->inOptions()->getTimeout());

        try{
            $resource = $streamClient->getConnect();
        } catch(\Exception $e)
        {
            throw new \Exception(sprintf(
                'Cannot connect to (%s).'
                , $this->inOptions()->getServerUrl()->toString()
                , $e->getCode()
                , $e ## as previous exception
            ));
        }

        $this->streamable = new Streamable($resource);
    }

    /**
     * Send Expression To Server
     *
     * - send expression to server through connection
     *   resource
     * - get connect if connection not stablished yet
     *
     * @param iHttpRequest|RequestInterface|string $expr Expression
     *
     * @throws ApiCallException
     * @return HttpResponse Prepared Server Response
     */
    function send($expr)
    {
        # prepare new request
        $this->isRequestComplete = false;

        ## destruct buffer
        $this->_getBufferStream()->getResource()->close();
        $this->_buffer = null;
        $this->_completed_response = null;

        # get connect if not
        if (!$this->isConnected() || !$this->streamable->getResource()->isAlive())
            $this->getConnect();

        if (is_string($expr))
            $expr = new HttpRequest($expr);
        elseif ($expr instanceof RequestInterface)
            ## convert PSR request to Poirot
            $expr = new HttpRequest($expr);

        if (!$expr instanceof iHttpRequest)
            throw new \InvalidArgumentException(sprintf(
                'Http Expression must instance of iHttpRequest, RequestInterface or string. given: "%s".'
                , \Poirot\Core\flatten($expr)
            ));


        # write stream
        try
        {
            $response = $this->__handleRequest($expr);
        } catch (\Exception $e) {
            $this->isRequestComplete = false;
            throw new ApiCallException(sprintf(
                'Request Call Error When Send To Server (%s)'
                , $this->streamable->getResource()->getRemoteName()
            ), 0, 1, __FILE__, __LINE__, $e);
        }

        $this->isRequestComplete = true;
        return $response;
    }

    /**
     * Send Request To Server
     *
     * @param iHttpRequest $expr
     * @return HttpResponse
     * @throws \Exception
     */
        protected function __handleRequest(iHttpRequest $expr)
        {
            $stream = $this->streamable;

            $emitter = $this->event()->trigger(TransporterHttpEvents::EVENT_REQUEST_SEND_PREPARE, [
                'request'     => $expr,
                'transporter' => $this,
            ]);

            /** @var iHttpRequest $expr */
            $expr = $emitter->collector()->getRequest();

            # send request, first headers
            $stream->write($expr->renderRequestLine());
            $stream->write($expr->renderHeaders());

            # send request body
            $body = $expr->getBody();
            if ($body !== null) {
                if (is_string($body))
                    $body = new Streamable\TemporaryStream($body);
                $body->pipeTo($stream);
            }

            # receive response headers once request sent
            $headersStr = $this->receive()->read();
            if (!$headersStr)
                throw new \Exception('Server not respond to this request.');
            $response   = new HttpResponse($headersStr);

            $emitter = $this->event()->trigger(TransporterHttpEvents::EVENT_RESPONSE_HEADERS_RECEIVED, [
                'response'    => $response,
                'transporter' => $this,
                'request'     => $expr,
            ]);


            if (!$emitter->collector()->getContinue())
                return $response;


            # receive rest response body
            $bodyStream = $this->receive();

            ## subset stream to body part without headers, seek will always point to body
            $bodyStream = new Streamable\SegmentWrapStream($bodyStream, -1, $bodyStream->getCurrOffset());
            $emitter = $this->event()->trigger(TransporterHttpEvents::EVENT_RESPONSE_BODY_RECEIVED, [
                'response'    => $response,
                'transporter' => $this,

                'body'        => $bodyStream,

                'request'     => $expr,
                'continue'    => false, ## no more request by default
            ]);

            $bodyStream = $emitter->collector()->getBody();
            $response->setBody($bodyStream);

            return $response;
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

    /**
     * Receive Server Response
     *
     * !! return response object if request completely sent
     *
     * - it will executed after a request call to server
     *   from send expression method to receive responses
     * - return null if request not sent or complete
     * - it must always return raw response body from server
     *
     * @throws \Exception No Connection established
     * @return null|string|Streamable
     */
    function receive()
    {
        if ($this->isRequestComplete())
            return null;

        ## so we can read later from latest position to end
        ## in example when we write header we can retrieve header next time
        $curSeek = $this->_buffer_seek;

        $stream = $this->streamable;

        if ($stream->getResource()->meta()->isTimedOut())
            throw new \RuntimeException(
                "Read timed out after {$this->inOptions()->getTimeout()} seconds."
            );

        while(!$stream->isEOF() && ($line = $stream->readLine("\r\n")) !== null ) {
            $break = false;
            $response = $line."\r\n";
            if (trim($line) === '') {
                ## http headers part read complete
                $response .= "\r\n";
                $break = true;
            }

            $this->_getBufferStream()->seek($this->_buffer_seek);
            $this->_getBufferStream()->write($response);
            $this->_buffer_seek += $this->_getBufferStream()->getTransCount();

            if ($break) break;
        }

        return $this->_getBufferStream()->seek($curSeek);
    }

        protected function _getBufferStream()
        {
            if (!$this->_buffer) {
                $this->_buffer = new Streamable\TemporaryStream();
                $this->_buffer_seek = 0;
            }

            return $this->_buffer;
        }

    /**
     * Is Connection Resource Available?
     *
     * @return bool
     */
    function isConnected()
    {
        return ($this->streamable !== null);
    }

    /**
     * Close Connection
     * @return void
     */
    function close()
    {
        if (!$this->isConnected())
            return;

        $this->streamable->getResource()->close();
        $this->streamable = null;
        $this->connected_options = null;
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
        if ($this->isConnected())
            ## the options will not changed when connected
            return $this->connected_options;

        return parent::inOptions();
    }

    /**
     * @override
     * @return HttpTransporterOptions
     */
    static function newOptions()
    {
        return new HttpTransporterOptions;
    }


    // ...

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
