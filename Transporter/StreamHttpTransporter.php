<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\ApiClient\AbstractConnection;
use Poirot\ApiClient\Exception\ApiCallException;
use Poirot\ApiClient\Exception\ConnectException;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Events\BaseEvents;
use Poirot\Events\Interfaces\iEvent;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Interfaces\Message\iHttpResponse;
use Poirot\Http\Message\HttpRequest;
use Poirot\Http\Message\HttpResponse;
use Poirot\Http\Psr\Interfaces\RequestInterface;
use Poirot\HttpAgent\Transporter\Listeners\onResponseReadHeaders;
use Poirot\Stream\Interfaces\iSResource;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Streamable;
use Poirot\Stream\StreamClient;

class StreamHttpTransporter extends AbstractConnection
    implements iEventProvider
{
    /** @var iSResource When Connected */
    protected $connected;
    /** @var  */
    protected $connected_options;
    /** @var StreamHttpEvents */
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
     * @throws ConnectException
     * @return void
     */
    function getConnect()
    {
        if ($this->isConnected())
            ## close current connection if connected
            $this->close();

        $streamClient = $this->__getSocketClient();

        # apply options to resource
        ## options will not take an affect after connect
        $this->connected_options = clone $this->options();

        ## determine protocol
        // TODO ssl connection with context bind
        $serverUrl = $this->options()->getServerUrl();
        if (!$serverUrl)
            throw new \RuntimeException('Server Url is Mandatory For Connect.');

        $serverUrl->setScheme('tcp');
        if (!$serverUrl->getPort())
            $serverUrl->setPort(80);

        $streamClient->setSocketUri($serverUrl->toString());

        ### options
        $streamClient->setPersistent($this->options()->getPersistent());
        $streamClient->setTimeout($this->options()->getTimeout());

        $this->connected = $streamClient->getConnect();
    }

    /**
     * Execute Expression
     *
     * - send expression to server through connection
     *   resource
     *
     * @param iHttpRequest|RequestInterface|string $expr Expression
     *
     * @throws ApiCallException
     * @return iHttpResponse
     */
    function exec($expr)
    {
        if ($expr instanceof RequestInterface)
            ## convert PSR request to Poirot
            $expr = new HttpRequest($expr);

        if (!$expr instanceof iHttpRequest && !is_string($expr))
            throw new \InvalidArgumentException(sprintf(
                'Http Expression must instance of iHttpRequest, RequestInterface or string. given: "%s".'
                , \Poirot\Core\flatten($expr)
            ));

        # get connect if not
        if (!$this->isConnected() || !$this->connected->isAlive())
            $this->getConnect();


        # write stream
        if (is_string($expr))
            $stream = $this->__sendPlainString($expr);
        else
            $stream = $this->__sendRequestObject($expr);


        # read response
        $response = $this->__readResponse($stream);
        return $response;
    }

    /**
     * Read Response From Server When Request Was Sent
     * @param iStreamable $stream
     * @return string
     */
    protected function __readResponse($stream)
    {
        $response = '';
        while($line = $stream->readLine("\r\n")) {
            $response .= $line."\r\n";
            if (trim($line) === '') {
                ## http headers part read complete
                $response .= "\r\n";
                break;
            }
        }

        $response = new HttpResponse($response);
        $this->event()->trigger(StreamHttpEvents::EVENT_RESPONSE_HEAD_READ, [
            'response'    => $response,
            'stream'      => $stream,
            'transporter' => $this,
        ]);

        return $response;
    }

    /**
     * Is Connection Resource Available?
     *
     * @return bool
     */
    function isConnected()
    {
        return ($this->connected !== null);
    }

    /**
     * Close Connection
     * @return void
     */
    function close()
    {
        if (!$this->isConnected())
            return;

        $this->connected->close();
        $this->connected_options = null;
    }


    // ...

    /**
     * Send Request To Server
     * @param string $expr
     * @return Streamable
     */
    protected function __sendPlainString($expr)
    {
        $stream = $this->__newStreamable();
        $stream->write($expr);

        return $stream;
    }

    /**
     * Send Request To Server
     * @param iHttpRequest $expr
     * @return Streamable
     */
    protected function __sendRequestObject(iHttpRequest $expr)
    {
        $stream = $this->__newStreamable();

        $stream->write($expr->renderRequestLine());
        $stream->write($expr->renderHeaders());

        $body = $expr->getBody();
        if ($body instanceof iStreamable)
            $body->pipeTo($stream);

        return $stream;
    }

    protected function __newStreamable()
    {
        return new Streamable($this->connected);
    }


    // ...

    /**
     * Get Events
     *
     * @return StreamHttpEvents
     */
    function event()
    {
        if (!$this->event)
            $this->event = new StreamHttpEvents;

        return $this->event;
    }

    /**
     * @override just for ide completion
     *
     * @return StreamHttpTransporterOptions
     */
    function options()
    {
        if ($this->isConnected())
            ## the options will not changed when connected
            return $this->connected_options;

        return parent::options();
    }

    /**
     * @override
     *
     * @return StreamHttpTransporterOptions
     */
    static function optionsIns()
    {
        return new StreamHttpTransporterOptions;
    }


    // ...

    /**
     * @return StreamClient
     */
    protected function __getSocketClient()
    {
        $client = new StreamClient;

        return $client;
    }

    protected function __attachDefaultListeners()
    {
        $this->event()->on(
            StreamHttpEvents::EVENT_RESPONSE_HEAD_READ
            , new onResponseReadHeaders
            , 100
        );
    }
}
