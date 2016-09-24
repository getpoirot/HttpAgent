<?php
namespace Poirot\HttpAgent;

use Poirot\HttpAgent\Platform\PlatformHttp;
use Traversable;

use Psr\Http\Message\StreamInterface;

use Poirot\ApiClient\Request\Command;

use Poirot\Stream\Streamable;


/*CommandRequestHttp([
    'uri' => '/',
    'method'  => HttpRequest::METHOD_GET,
    'browser_options' => [
        'base_url'   => 'http://raya-media.com/page',
        'user_agent' => 'Payam Browser',
        'connection_options' => [
            'time_out' => 10,
            'persist'  => true,
            'allow_decoding' => false,
        ],
    ]
])*/

class CommandRequestHttp
    extends Command
{
    protected $method = 'GET';
    /** @var string */
    protected $host;
    /** @var string Request Target Uri */
    protected $target;
    /** @var array */
    protected $headers;
    /** @var StreamInterface|string|null */
    protected $body;

    /** @var array|\Traversable Browser Specific Options */
    protected $platformSettings;


    /**
     * Construct
     *
     * @param array|\Traversable $options
     */
    function __construct($options = null)
    {
        if ($options !== null) {
            if ($options instanceof Traversable)
                $options = \Poirot\Std\cast($options)->toArray();

            if (!is_array($options))
                throw new \InvalidArgumentException(sprintf(
                    'Options must be array or Traversable; given: (%s).'
                    , \Poirot\Std\flatten($options)
                ));

            if (!isset($options['arguments']))
                $options['arguments'] = $options;
        }
        
        parent::__construct($options);
    }
    
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
            'host'    => $this->getHost(),
            'uri'     => $this->getTarget(),
            'headers' => $this->getHeaders(),
            'body'    => $this->getBody(),
            'browser_options' => $this->getPlatformSettings(),
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
     * @see PlatformHttp::_makeExpressionRequest
     */
    
    /**
     * Set Request Method Type
     * exp. GET, POST, ...
     *
     * @param string $method
     * @return $this
     */
    function setMethod($method)
    {
        $this->method = (string) $method;
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
     * Host Name Include Protocol Scheme
     * exp. http://host-name.com or host-name.com
     *
     * default protocol is http
     *
     * @param string $host
     * @return $this
     */
    function setHost($host)
    {
        $this->host = rtrim( (string) $host, '/' );
        return $this;
    }

    /**
     * @return string
     */
    function getHost()
    {
        return $this->host;
    }

    /**
     * Set Request Target Uri To Server
     * exp. /path/to/resource
     *
     * @param string $target
     * @return $this
     */
    function setTarget($target)
    {
        $this->target = (string) $target;
        return $this;
    }

    /**
     * @return string|null
     */
    function getTarget()
    {
        return $this->target;
    }

    /**
     * @param array $headers ['headerName'=>$value, ]
     * @return $this
     */
    function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @see aHttpMessage::setHeaders used on platform
     * @return array|null
     */
    function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set Request Body
     * @param string|StreamInterface $body
     * @return $this
     */
    function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get Request Body
     * @return null|StreamInterface
     */
    function getBody()
    {
        return $this->body;
    }

    
    /** 
     * @see Browser::setPlatformSetting
     * @see PlatformHttp // Options:
     */ 
    
    /**
     * Set Platform Specific Options
     *
     * note: also registered platform plugins options include here
     *
     * @param array|\Traversable $platformSettings
     * @return $this
     */
    function setPlatformSettings($platformSettings)
    {
        $this->platformSettings = $platformSettings;
        return $this;
    }

    /**
     * Get Platform Specific Options
     *
     * note: also registered platform plugins options include here
     *
     * @return array|\Traversable
     */
    function getPlatformSettings()
    {
        return $this->platformSettings;
    }
}
