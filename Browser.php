<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\AbstractClient;
use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\ApiClient\Interfaces\Request\iApiMethod;
use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Core\Interfaces\iOptionsProvider;
use Poirot\Core\Traits\CloneTrait;
use Poirot\Http\Interfaces\iHeaderCollection;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Message\HttpRequest;
use Poirot\HttpAgent\Browser\HttpPlatform;
use Poirot\HttpAgent\Browser\ResponsePlatform;
use Poirot\HttpAgent\Transporter\StreamHttpTransporter;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Interfaces\iSeqPathUri;
use Poirot\PathUri\Psr\UriInterface;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Psr\StreamInterface;

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
*/

class Browser extends AbstractClient
    implements iOptionsProvider
{
    use CloneTrait;

    /** @var StreamHttpTransporter|iConnection*/
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
     * @return StreamHttpTransporter
     */
    function connection()
    {
        if (!$this->connection)
            $this->connection = new StreamHttpTransporter;

        return $this->connection;
    }


    // ...

    /**
     * @param string|iSeqPathUri|iHttpUri|UriInterface $uri
     * @param array|iHeaderCollection|null             $headers
     * @param array|iDataSetConveyor|null              $options
     *
     * @return ResponsePlatform
     */
    function GET($uri, $headers = null, $options = null)
    {
        $method = new ReqMethod([
            'uri' => $uri,
            'method'  => HttpRequest::METHOD_GET,
        ]);

        ($headers === null) ?: $method->setHeaders($headers);
        ($options === null) ?: $method->setBrowser($options);

        $response = $this->call($method);
        return $response;
    }

    /**
     * Send HTTP OPTIONS request to server
     *
     * - using absolute url as target not depend on current request base url
     *
     * - create method build from platform, platform will build request object from that
     *
     * @param string|iHttpUri|UriInterface $uri Relative Uri that merged into base url
     *
     * @return iHttpRequest
     */
    function OPTIONS($uri) {}

    function HEAD($uri, $headers = null, $options = null) {}

    /**
     * @param string|iSeqPathUri|iHttpUri|UriInterface         $uri
     * @param string|iStreamable|StreamInterface|resource|null $body
     * @param array|iHeaderCollection|null                     $headers
     * @param array|BrowserOptions|iDataSetConveyor|null       $options
     *                                                         Agent Options To Merge With Default Agent Options
     */
    function PATCH($uri, $body = null, $headers = null, $options = null) {}

    function POST($uri, $body = null, $headers = null, $options = null) {}

    function PUT($uri, $body = null, $headers = null, $options = null) {}

    function DELETE($uri, $body = null, $headers = null, $options = null) {}

    function TRACE($uri) {}

    function CONNECT($uri) {}


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
        // $this->connection()->close();
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
        return parent::__call($methodName, $args);
    }

}
