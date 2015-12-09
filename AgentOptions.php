<?php
namespace Poirot\HttpAgent;

use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Core\OpenOptions;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Message\HttpRequest;
use Poirot\Http\Psr\Interfaces\RequestInterface;
use Poirot\HttpAgent\Connection\HAStreamConn;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Psr\UriInterface;

/**
 * This is open options because may contains options for attached plugins
 */
class AgentOptions extends OpenOptions
{
    /** @var string|iHttpUri|UriInterface Base Url to Server */
    protected $baseUrl;

    # default element options
    protected $connection;
    protected $request;

    protected $userAgent;

    /**
     * @return iHttpUri|UriInterface|string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param iHttpUri|UriInterface|string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set Connection Options
     *
     * @param array|iDataSetConveyor|HAStreamConn $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array|iDataSetConveyor|HAStreamConn
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set Base Request Options as Default
     *
     * @param string|array|iHttpRequest|HttpRequest|RequestInterface $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return string|array|iHttpRequest|HttpRequest|RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    // ...

    /**
     * @param mixed $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }
}
