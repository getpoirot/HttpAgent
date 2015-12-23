<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\Request\Method;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Core\Traits\OptionsTrait;
use Poirot\Http\Headers;
use Poirot\Http\Interfaces\iHeaderCollection;
use Poirot\PathUri\HttpUri;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Interfaces\iSeqPathUri;
use Poirot\PathUri\Psr\UriInterface;
use Poirot\Stream\Interfaces\iStreamable;

class ReqMethod extends Method
{
    use OptionsTrait;

    protected $method = 'GET';
    /** @var iHttpUri|iSeqPathUri Request Target Uri */
    protected $uri;
    /** @var iHeaderCollection|Headers */
    protected $headers;
    /** @var iStreamable|string|null */
    protected $body;

    /** @var BrowserOptions Browser Specific Options */
    protected $browser;

    /**
     * Construct
     *
     * - Build Method From Setter Setup Options
     *   'namespaces'
     *   'method'
     *   'arguments'
     *
     * @param array $setupSetter
     */
    function __construct(array $setupSetter = null)
    {
        parent::__construct($setupSetter);

        $this->_t_options__internal = [
            'setArguments', 'getArguments' ## these methods will ignore as option in prop
        ];
    }

    // override:

    /**
     * Set Method Arguments
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
        $this->fromArray($args);
        return $this;
    }

    /**
     * Get Method Arguments
     *
     * - we can define default arguments with some
     *   values
     *
     * @return array
     */
    function getArguments()
    {
        return $this->toArray();
    }

    // options:

    /**
     * Set Request Method Type
     * exp. GET, POST, ...
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = strtoupper((string) $method);
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set Request Uri To Server
     *
     * @param iHttpUri|UriInterface|iSeqPathUri|string $uri
     * @return $this
     */
    public function setUri($uri)
    {
        if (is_string($uri) || $uri instanceof UriInterface) {
            $uri = new HttpUri($uri);
            if (!$uri->getScheme() && !$uri->getHost())
                ## this is sequent path
                $uri = $uri->getPath();
        }

        if (!$uri instanceof iHttpUri && !$uri instanceof iSeqPathUri)
            throw new \InvalidArgumentException(sprintf(
                'Uri must instance of iHttpUri, UriInterface, iSeqPathUri or string. given: "%s"'
                , \Poirot\Core\flatten($uri)
            ));

        $this->uri = $uri;
        return $this;
    }

    /**
     * @return iHttpUri|iSeqPathUri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param iHeaderCollection|array $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        if (is_array($headers))
            $headers = new Headers($headers);

        if (!$headers instanceof iHeaderCollection)
            throw new \InvalidArgumentException;

        $this->headers = $headers;
        return $this;
    }

    /**
     * @return Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set Request Body
     * @param string|iStreamable $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return null|string|iStreamable
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set Browser Specific Options
     * @param array|iDataSetConveyor|BrowserOptions $browser
     * @return $this
     */
    public function setBrowser($browser)
    {
        if (!$browser instanceof BrowserOptions)
            $browser = new BrowserOptions($browser);

        $this->browser = $browser;
        return $this;
    }

    /**
     * @return BrowserOptions
     */
    public function getBrowser()
    {
        if (!$this->browser)
            $this->browser = new BrowserOptions;

        return $this->browser;
    }
}