<?php
namespace Poirot\HttpAgent\Connection;

use Poirot\ApiClient\AbstractConnection;
use Poirot\ApiClient\Exception\ApiCallException;
use Poirot\ApiClient\Exception\ConnectException;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Message\HttpRequest;
use Poirot\Http\Psr\Interfaces\RequestInterface;
use Poirot\Stream\Interfaces\iSResource;
use Poirot\Stream\Streamable;
use Poirot\Stream\StreamClient;

class HAStreamConn extends AbstractConnection
{
    /** @var StreamClient */
    protected $resource;
    /** @var iSResource When Connected */
    protected $connected;

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


        $resource = $this->getResourceOrigin();

        # apply options to resource
        // ..

        $this->connected = $resource->getConnect();
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
     * @return mixed Server Result
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

        $stream = new Streamable($this->connected);

        # prepare request headers and ....
        // ...

        # handle request object
        // ...

        # send expression to server
        $stream->write($expr);

        # read response
        $response = $stream->read();

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
     * Get Connection Resource Origin
     *
     * ! in case of streams connection it will return
     *   open read stream resource
     *
     * @return StreamClient
     */
    function getResourceOrigin()
    {
        if (!$this->resource)
            $this->resource = new StreamClient;

        return $this->resource;
    }

    /**
     * Close Connection
     * @return void
     */
    function close()
    {
        if ($this->isConnected())
            $this->connected->close();
    }


    // ...

    /**
     * @override just for ide completion
     *
     * @return HAStreamOpts
     */
    function options()
    {
        return parent::options();
    }

    /**
     * @override
     *
     * @return HAStreamOpts
     */
    static function optionsIns()
    {
        return new HAStreamOpts;
    }
}
