<?php
namespace Poirot\HttpAgent\Browser;

use Poirot\Http\Interfaces\iHeaders;
use Poirot\Std\Struct\aDataOptions;
use Psr\Http\Message\StreamInterface;

/**
 * @see \Poirot\Http\HttpRequest
 */
class DataOptionsBrowserRequest 
    extends aDataOptions
{
    protected $version;
    protected $headers;
    protected $body;
    protected $method;
    protected $host;
    protected $target_uri;

    /**
     * Set Version
     *
     * @param string $ver
     *
     * @return $this
     */
    function setVersion($ver)
    {
        $this->version = (string) $ver;
        return $this;
    }

    /**
     * Get Version
     *
     * @return string
     */
    function getVersion()
    {
        return $this->version;
    }

    /**
     * Set message headers or headers collection
     *
     * ! HTTP messages include case-insensitive header
     *   field names
     *
     * ! headers may contains multiple values, such as cookie
     *
     * @param array|iHeaders $headers
     *
     * @return $this
     */
    function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Get Headers collection
     *
     * @return array|iHeaders|null
     */
    function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set Message Body Content
     *
     * @param string|StreamInterface|null $content
     *
     * @return $this
     */
    function setBody($content)
    {
        $this->body = $content;
        return $this;
    }

    /**
     * Get Message Body Content
     *
     * @return string|StreamInterface|null
     */
    function getBody()
    {
        return $this->body;
    }
    
    /**
     * Set Request Method
     *
     * @param string $method
     *
     * @return $this
     */
    function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * Get Request Method
     *
     * @return string
     */
    function getMethod()
    {
        return $this->method;
    }

    /**
     * Set Host
     *
     * note: Host header typically mirrors the host component of the URI,
     *       However, the HTTP specification allows the Host header to
     *       differ from each of the two.
     *
     * @param string $host
     *
     * @return $this
     */
    function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Get Host
     *
     * - During construction, implementations MUST
     *   attempt to set the Host header from a provided
     *   URI if no Host header is provided.
     *
     * @throws \Exception
     * @return string
     */
    function getHost()
    {
        return $this->host;
    }

    /**
     * Set Uri Target
     *
     * @param string $target
     *
     * @return $this
     */
    function setTarget($target = null)
    {
        $this->target_uri = (string) $target;
        return $this;
    }

    /**
     * Get Uri Target
     *
     * - return "/" if no one composed
     *
     * @return string
     */
    function getTarget()
    {
        return $this->target_uri;
    }
}
