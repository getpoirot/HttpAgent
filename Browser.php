<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\AbstractClient;
use Poirot\ApiClient\Interfaces\iTransporter;
use Poirot\ApiClient\Interfaces\Request\iApiMethod;
use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Core\Interfaces\iOptionsProvider;
use Poirot\Core\Traits\CloneTrait;
use Poirot\Http\Interfaces\iHeaderCollection;
use Poirot\Http\Message\HttpRequest;
use Poirot\HttpAgent\Browser\HttpPlatform;
use Poirot\HttpAgent\Browser\ResponsePlatform;
use Poirot\HttpAgent\Transporter\HttpStreamTransporter;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Interfaces\iSeqPathUri;
use Poirot\PathUri\Psr\UriInterface;
use Poirot\Stream\Interfaces\iStreamable;

/*
$browser = new Browser('http://google.com/about', [
    'connection' => [
        'time_out' => 30,
        'persist'  => false,
    ],
]);

$method = new ReqMethod([
    'uri' => '/',
    'method'  => HttpRequest::METHOD_GET,
    'browser' => [
        'base_url'   => 'http://raya-media.com/page',
        'user_agent' => 'Payam Browser',
        'connection' => [
            'time_out' => 10,
            'persist'  => true,
            'allow_decoding' => false,
        ],
    ]
]);

$response = $browser->call($method);

// ================================================================

$browser->custom(
    'http://www.pasargad-co.ir/forms/contact'
    , [
        'connection' => ['time_out' => 30],
        'request'    => [
            'headers' => [
                'X-data' => 'extra header data'
            ]
            'uri_options' => [
                'query'     => 'first=value&arr[]=foo+bar&arr[]=baz',
                'fragment'  => 'fragment',
            ]
        ],
    ]
    , 'Salam Pasargad e Khar'
    , ['this' => 'header']
)->getResult(function($response) {
    $response->flush(false);
});

*/

/**
 * TODO call request object
 *      $this->doRequest(HttpRequest)
 */
class Browser extends AbstractClient
    implements iOptionsProvider
{
    use CloneTrait;

    /** @var HttpStreamTransporter|iTransporter*/
    protected $connection;
    /** @var HttpPlatform */
    protected $platform;

    /** @var BrowserOptions */
    protected $options;

    /**
     * Construct
     *
     * - construct('http://google.com', ['connection' => ['time_out' => 20]]);
     * - construct([
     *    'base_url'    => 'http://google.com'
     *    'connection'  => ['time_out' => 20]
     * ]);
     *
     * @param BrowserOptions|iDataSetConveyor|null|string $baseUrlOrOptions
     * @param array|null                                  $ops     Options when using as base_url
     */
    function __construct($baseUrlOrOptions = null, $ops = null)
    {
        if ($baseUrlOrOptions !== null && is_string($baseUrlOrOptions))
            $this->inOptions()->setBaseUrl($baseUrlOrOptions);
        elseif ($baseUrlOrOptions !== null)
            $ops = $baseUrlOrOptions;

        if ($ops !== null)
            $this->inOptions()->from($ops);
    }

    /**
     * Get Client Platform
     *
     * - used by request to build params for
     *   server execution call and response
     *
     * @return HttpPlatform
     */
    function platform()
    {
        if (!$this->platform)
            $this->platform = new HttpPlatform($this);

        return $this->platform;
    }

    /**
     * Get Connection Adapter
     *
     * @return HttpStreamTransporter
     */
    function transporter()
    {
        if (!$this->connection)
            $this->connection = new HttpStreamTransporter;

        return $this->connection;
    }


    // ...
    /** @link http://www.tutorialspoint.com/http/http_methods.htm */

    /**
     * @return ResponsePlatform
     */
    function GET($uri, $options = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_GET, $uri, $options, null, $headers);
    }

    /**
     * note: For retrieving meta-information written in response headers,
     *       without having to transport the entire content(body).
     *
     * @return ResponsePlatform
     */
    function HEAD($uri, $options = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_HEAD, $uri, $options, null, $headers);
    }

    /**
     * ! post request should always has Content-Length Header if has body
     *   with value equals to body size
     *   @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.4
     *
     * @return ResponsePlatform
     */
    function POST($uri, $options = null, $body = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_POST, $uri, $options, $body, $headers);
    }

    /**
     * note: PUT puts a file or resource at a specific URI, and exactly at that URI.
     *       If there's already a file or resource at that URI, PUT replaces that
     *       file or resource. If there is no file or resource there, PUT creates one.
     *
     * @return ResponsePlatform
     */
    function PUT($uri, $options = null, $body = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_PUT, $uri, $options, $body, $headers);
    }

    /**
     * note: Used to update partial resources.
     *       For instance, when you only need to update one field of the resource,
     *       PUTting a complete resource representation might be cumbersome and
     *       utilizes more bandwidth.
     *
     * @return ResponsePlatform
     */
    function PATCH($uri, $options = null, $body = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_PATCH, $uri, $options, $body, $headers);
    }

    /**
     * note: Removes all current representations of the target resource given by a URI
     * @return ResponsePlatform
     */
    function DELETE($uri, $options = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_DELETE, $uri, $options, null, $headers);
    }

    /**
     * note: Allows the client to determine the options and/or requirements
     *       associated with a resource, or the capabilities of a server, without
     *       implying a resource action or initiating a resource retrieval.
     *
     * @return ResponsePlatform
     */
    function OPTIONS($uri, $options = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_OPTIONS, $uri, $options, null, $headers);
    }

    /**
     * note: used by the client to establish a network connection to
     *       a web server over HTTP
     *
     * @return ResponsePlatform
     */
    function CONNECT($uri, $options = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_CONNECT, $uri, $options, null, $headers);
    }

    /**
     * note: used to echo the contents of an HTTP Request back to the requester
     *       which can be used for debugging purpose at the time of development
     *
     * @return ResponsePlatform
     */
    function TRACE($uri, $options = null, $headers = null)
    {
        return $this->__makeRequestCall(HttpRequest::METHOD_TRACE, $uri, $options, null, $headers);
    }

    /**
     * Send Http Request Message
     *
     * @param HttpRequest $request
     * @param null $options
     *
     * @return ResponsePlatform
     */
    function request(HttpRequest $request, $options = null)
    {
        $uri = $request->getUri();
        return $this->__makeRequestCall($request->getMethod(), $uri, $options, null, $request->getHeaders());
    }

    /**
     * Make Request Method Call To Server
     *
     * @param string|iSeqPathUri|iHttpUri|UriInterface $uri     Absolute Uri Or Relative To BaseUrl
     * @param array|iDataSetConveyor|null              $options Browser Options Or Open Options Used By Plugins
     * @param iStreamable|string|null                  $body    Request Body
     * @param array|iHeaderCollection|null             $headers Specific Request Header/Replace Defaults
     *
     * @return ResponsePlatform
     */
    protected function __makeRequestCall($method, $uri, $options=null, $body=null, $headers=null)
    {
        $method = new ReqMethod([
            'uri'    => $uri,
            'method' => $method,
        ]);

        ($options === null) ?: $method->setBrowser($options);
        ($body    === null) ?: $method->setBody($body);
        ($headers === null) ?: $method->setHeaders($headers);

        $response = $this->call($method);
        return $response;
    }

    // ...

    /**
     * @return BrowserOptions
     */
    function inOptions()
    {
        if (!$this->options)
            $this->options = static::newOptions();

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
     * @return BrowserOptions
     */
    static function newOptions()
    {
        return new BrowserOptions;
    }


    // ...

    /**
     * @override Ide Completion
     * @param iApiMethod $method
     * @return ResponsePlatform
     */
    function call(iApiMethod $method)
    {
        $return = parent::call($method);
        return $return;
    }

    /**
     * @override Ide Completion
     * @param string $methodName
     * @param array  $args
     * @return ResponsePlatform
     */
    function __call($methodName, $args)
    {
        ## Named arguments
        /*ReqMethod([
            'uri' => '/',
            'method'  => HttpRequest::METHOD_GET,
            'browser' => [
                'base_url'   => 'http://raya-media.com/page',
                'user_agent' => 'Payam Browser',
                'connection' => [
                    'time_out' => 10,
                    'persist'  => true,
                    'allow_decoding' => false,
                ],
            ]
        ])*/

        ## Positional Params
        // method()($uri, $headers = null, $body = null, $options = null)
        if (count($args) >= 0 && isset($args[0]) && !is_array($args[0])) {
            $curr = $args;
            $args = [];
            (!isset($curr[0])) ?: $args['uri']     = $curr[0];
            (!isset($curr[1])) ?: $args['browser'] = $curr[1];
            (!isset($curr[2])) ?: $args['body']    = $curr[2];
            (!isset($curr[3])) ?: $args['headers'] = $curr[3];
        }

        return parent::__call($methodName, $args);
    }
}
