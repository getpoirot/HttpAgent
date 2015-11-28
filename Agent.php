<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\AbstractClient;
use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\Core\BuilderSetterTrait;
use Poirot\Http\Interfaces\Message\iHttpResponse;
use Poirot\Http\Message\HttpRequest;
use Poirot\HttpAgent\Interfaces\iHADriver;
use Poirot\HttpAgent\Interfaces\iHAgentOptions;
use Poirot\HttpAgent\Interfaces\iHttpAgent;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Psr\Interfaces\RequestInterface;

class Agent extends AbstractClient
    implements iHttpAgent
{
    use BuilderSetterTrait;

    /** @var HttpRequest */
    protected $request;
    /** @var AgentOptions */
    protected $options;
    /** @var iHttpResponse */
    protected $response = false;

    /**
     * Construct
     *
     * @param string $baseUri
     * @param array  $options
     */
    function __construct($baseUri = null, $options = null)
    {
        if (is_array($baseUri)) {
            if ($options !== null)
                throw new \InvalidArgumentException;

            $options = $baseUri;
        }

        if ($options !== null) {
            ## first build Agent with setter options
            $options = $this->setupFromArray($options);
            ## then set options from remained
            $this->options()->from($options);
        }
    }

    /**
     * Request Http
     *
     * @return iHttpRequest
     */
    function request()
    {
        if(!$this->request)
            $this->request = new HttpRequest;

        return $this->request;
    }

    /**
     * Send Http Request
     *
     * - use given request argument if exists instead of
     *   request object within client and return clone of
     *   http client with given request
     *
     * - it must replace with current response
     *
     * @param RequestInterface|null $request
     * @param array                 $options TODO synchronus or Async
     *
     * @return iHttpAgent
     */
    function send(RequestInterface $request = null, $options = [])
    {
        // TODO: Implement send() method.
    }

    /**
     * Response
     *
     * - response exists after send any request
     *   it will replaced after each send call
     *
     * @return iHttpResponse|false Request was not send yet
     */
    function response()
    {
        return $this->response;
    }


    // ...

    /**
     * Set Request
     *
     * @param iHttpRequest|RequestInterface $request
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setRequest($request)
    {
        if ($request instanceof RequestInterface)
            $request = new HttpRequest($request);

        if (!$request instanceof iHttpRequest)
            throw new \InvalidArgumentException(sprintf(
                'Request must instance of iHttpRequest or RequestInterface, given: (%s).'
                , \Poirot\Core\flatten($request)
            ));

        $this->request = $request;

        return $this;
    }

    /**
     * Set Send/Receive Wire Driver
     *
     * ! array with driver options
     *   [ 'driver_name' => [..options] ]
     *
     * @param iHADriver|array|string $driver
     *
     * @return $this
     */
    function setConnection($driver)
    {

    }

    /**
     * Set Driver Options
     *
     * @param array $options
     *
     * @return $this
     */
    function setConnectionOptions(array $options)
    {

    }


    // ...

    /**
     * @return iHAgentOptions
     */
    function options()
    {
        if (!$this->options)
            $this->options = self::optionsIns();

        return $this->options;
    }

    /**
     * Get An Bare Options Instance
     *
     * ! it used on easy access to options instance
     *   before constructing class
     *   [php]
     *      $opt = Filesystem::optionsIns();
     *      $opt->setSomeOption('value');
     *
     *      $class = new Filesystem($opt);
     *   [/php]
     *
     * @return iHAgentOptions
     */
    static function optionsIns()
    {
        return new AgentOptions;
    }


    // ...

    function __clone()
    {
        (!$this->request) ?: $this->request = clone $this->request;
        (!$this->options) ?: $this->options = clone $this->options;
    }

    /**
     * Get Client Platform
     *
     * - used by request to build params for
     *   server execution call and response
     *
     * @return iPlatform
     */
    function platform()
    {
        // TODO: Implement platform() method.
    }

    /**
     * Get Connection Adapter
     *
     * @return iConnection
     */
    function connection()
    {
        // TODO: Implement connection() method.
    }
}
