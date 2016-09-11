<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\Request\Command;
use Poirot\Http\Header\CollectionHeader;
use Poirot\Http\Interfaces\iHeaders;
use Poirot\Stream\Interfaces\iStreamable;

class CommandRequestHttp
    extends Command
{
    protected $method = 'GET';
    /** @var string Request Target Uri */
    protected $uri;
    /** @var iHeaders|CollectionHeader */
    protected $headers;
    /** @var iStreamable|string|null */
    protected $body;

    /** @var BrowserOptions Browser Specific Options */
    protected $browserOptions;


    // override:

    /**
     * Set Method Arguments
     * @ignore by OptionsTrait
     *
     * - it will replace current arguments
     * - use empty array to clear arguments
     *
     * @param array $args Arguments
     *
     * @return $this
     */
    function setArguments(array $args)
    {
        $this->with($args);
        return $this;
    }

    /**
     * Get Method Arguments
     * @ignore by OptionsTrait
     *
     * - we can define default arguments with some
     *   values
     *
     * @return array
     */
    function getArguments()
    {
        $args = array(
            'method'  => $this->getMethod(),
            'uri'     => $this->getUri(),
            'headers' => $this->getHeaders(),
            'body'    => $this->getBody(),
            'browser_options' => $this->getBrowserOptions(),
        );
        
        return $args;
    }

    /**
     * Clear All Arguments (to it's default)
     * @return $this
     * @throws \Exception
     */
    function clearArguments()
    {
        throw new \Exception('Not Implemented.');
    }
    
    // options:

    /**
     * Set Request Method Type
     * exp. GET, POST, ...
     *
     * @param string $method
     * @return $this
     */
    function setMethod($method)
    {
        $this->method = strtoupper((string) $method);
        return $this;
    }

    /**
     * @return string
     */
    function getMethod()
    {
        return $this->method;
    }

    /**
     * Set Request Uri To Server
     *
     * @param string $uri
     * @return $this
     */
    function setUri($uri)
    {
        $this->uri = (string) $uri;
        return $this;
    }

    /**
     * @return string|null
     */
    function getUri()
    {
        return $this->uri;
    }

    /**
     * @param iHeaders|array $headers
     * @return $this
     */
    function setHeaders($headers)
    {
        if (is_array($headers))
            $headers = new CollectionHeader($headers);

        if (!$headers instanceof iHeaders)
            throw new \InvalidArgumentException;

        $this->headers = $headers;
        return $this;
    }

    /**
     * @return CollectionHeader
     */
    function getHeaders()
    {
        if (!$this->headers)
            $this->headers = new CollectionHeader();
        
        return $this->headers;
    }

    /**
     * Set Request Body
     * @param string|iStreamable $body
     */
    function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return null|string|iStreamable
     */
    function getBody()
    {
        return $this->body;
    }

    /**
     * Set Browser Specific Options
     * @param array|\Traversable|BrowserOptions $browserOptions
     * @return $this
     */
    function setBrowserOptions($browserOptions)
    {
        $this->getBrowserOptions()->import($browserOptions);
        return $this;
    }

    /**
     * @return BrowserOptions
     */
    function getBrowserOptions()
    {
        if (!$this->browserOptions)
            $this->browserOptions = new BrowserOptions;

        return $this->browserOptions;
    }
}
