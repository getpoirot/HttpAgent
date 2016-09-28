<?php
namespace Poirot\HttpAgent;

use Poirot\PathUri\UriHttp;
use Poirot\PathUri\UriSequence;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

use Poirot\ApiClient\aClient;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;

use Poirot\Std\Interfaces\Pact\ipConfigurable;

use Poirot\HttpAgent\Platform\PlatformHttp;
use Poirot\HttpAgent\Platform\ResponsePlatform;


/**
 * TODO decompress gzip response with chunked data not working
 */
class Browser extends aClient
{
    /** @var PlatformHttp */
    protected $platform;

    # options
    /** @var string Base Url to Server */
    protected $baseUrl;
    protected $userAgent;
    /** @var array */
    protected $pluginOptions;


    /**
     * Construct
     *
     * - construct('http://google.com', ['platform_settings' => ['connection_settings' => 'time_out' => 20]]);
     * - construct([
     *    'base_url'          => 'http://google.com'
     *    'platform_settings' => ['connection_settings' => 'time_out' => 20]
     * ]);
     *
     * @param \Traversable|null|string $baseUrlOrOptions
     * @param array|null                                  $ops               Options when using as base_url
     */
    function __construct($baseUrlOrOptions = null, $ops = null)
    {
        if ($baseUrlOrOptions !== null && \Poirot\Std\isStringify($baseUrlOrOptions))
            $this->setBaseUrl($baseUrlOrOptions);
        elseif ($baseUrlOrOptions !== null)
            $ops = $baseUrlOrOptions;

        parent::__construct($ops);
    }

    /**
     * Get Client Platform
     *
     * - used by request to build params for
     *   server execution call and response
     *
     * @return PlatformHttp
     */
    function platform()
    {
        if (!$this->platform)
            // Bind same object options into Platform to sync. changes!!
            $this->platform = new PlatformHttp;

        return $this->platform;
    }
    
    /**
     * @override Ide Completion
     * @param iApiCommand $command
     * @return ResponsePlatform
     */
    function call(iApiCommand $command)
    {
        $return = parent::call($command);
        return $return;
    }
    
    /**
     * Send Http Request Message
     *
     * @param RequestInterface $request
     * @param null             $options
     *
     * @return ResponsePlatform
     */
    function request(RequestInterface $request, $options = null)
    {
        $uri = $request->getUri();
        if (!$uri)
            // use alternative
            $uri = $request->getRequestTarget();


        $headers = array();
        foreach ($request->getHeaders() as $name => $_)
            $headers[$name] = $request->getHeaderLine($name);

        $command = $this->_makeRequestCommand($request->getMethod(), $uri, $options, $request->getBody(), $headers);
        return $this->call($command);
    }


    /** @link http://www.tutorialspoint.com/http/http_methods.htm */

    /**
     * @return ResponsePlatform
     */
    function GET($uri, $options = null, $headers = null)
    {
        $command = $this->_makeRequestCommand('GET', $uri, $options, null, $headers);
        return $this->call($command);
    }

    /**
     * note: For retrieving meta-information written in response headers,
     *       without having to transport the entire content(body).
     *
     * @return ResponsePlatform
     */
    function HEAD($uri, $options = null, $headers = null)
    {
        $command = $this->_makeRequestCommand('HEAD', $uri, $options, null, $headers);
        return $this->call($command);
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
        $command = $this->_makeRequestCommand('POST', $uri, $options, $body, $headers);
        return $this->call($command);
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
        $command = $this->_makeRequestCommand('PUT', $uri, $options, $body, $headers);
        return $this->call($command);
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
        $command = $this->_makeRequestCommand('PATCH', $uri, $options, $body, $headers);
        return $this->call($command);
    }

    /**
     * note: Removes all current representations of the target resource given by a URI
     * @return ResponsePlatform
     */
    function DELETE($uri, $options = null, $headers = null)
    {
        $command = $this->_makeRequestCommand('DELETE', $uri, $options, null, $headers);
        return $this->call($command);
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
        $command = $this->_makeRequestCommand('OPTIONS', $uri, $options, null, $headers);
        return $this->call($command);
    }

    /**
     * note: used by the client to establish a network connection to
     *       a web server over HTTP
     *
     * @return ResponsePlatform
     */
    function CONNECT($uri, $options = null, $headers = null)
    {
        $command = $this->_makeRequestCommand('CONNECT', $uri, $options, null, $headers);
        return $this->call($command);
    }

    /**
     * note: used to echo the contents of an HTTP Request back to the requester
     *       which can be used for debugging purpose at the time of development
     *
     * @return ResponsePlatform
     */
    function TRACE($uri, $options = null, $headers = null)
    {
        $command = $this->_makeRequestCommand('TRACE', $uri, $options, null, $headers);
        return $this->call($command);
    }


    // Options:

    /**
     * exp.
     *
     *   http://site-name.com/
     *   http://site-name.com/basepath/
     *
     * @param string $baseUrl
     * @return $this
     */
    function setBaseUrl($baseUrl)
    {
        $this->baseUrl = (string) $baseUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    function getBaseUrl()
    {
        return $this->baseUrl;
    }
    

    /**
     * Set Platform Settings
     * 
     * @param mixed $options
     * 
     * @return $this
     * @throws \Exception
     */
    function setPlatformSetting($options)
    {
        $platform = $this->platform();
        if (!$platform instanceof ipConfigurable)
            throw new \Exception(sprintf(
                'Platform (%s) is not configurable.'
            ));

        $platform->with($platform::parseWith($options));
        return $this;
    }
    
    // ...
    
    /**
     * Make Request Command To Call To Server
     *
     * @param string                      $method  Request Method
     * @param string                      $uri     Absolute Uri Or Relative To BaseUrl
     * @param array|\Traversable|null     $options Platform Setting Or Open Options Used By Plugins
     * @param StreamInterface|string|null $body    Request Body
     * @param array|null                  $headers Specific Request Header/Replace Defaults
     *
     * @return CommandRequestHttp
     */
    protected function _makeRequestCommand($method, $uri, $options=null, $body=null, $headers=null)
    {
        $command = new CommandRequestHttp();

        $command->setMethod($method);

        # attain host / target uri ...
        $host = null;
        
        $reqUrl = new UriHttp(UriHttp::parseWith((string) $uri));
        if (!$reqUrl->isAbsolute()) {
            if ($baseUrl = $this->getBaseUrl())
            {
                ## Replacement of BaseUrl to requested Uri ..
                // query, fragment params must not included

                ### Host
                $baseUrl = new UriHttp(UriHttp::parseWith($baseUrl));
                $reqUrl->setScheme($baseUrl->getScheme());
                // request authority
                $reqUrl->setHost($baseUrl->getHost());
                $reqUrl->setUserInfo($baseUrl->getUserInfo());
                $reqUrl->setPort($baseUrl->getPort());

                ## Path
                $pathPre = new UriSequence($baseUrl->getPath());
                $reqUrl->prepend($pathPre);
            }
        }

        // TODO what if authority user-info exists how to connect to server with authority?
        $command->setHost($reqUrl->getScheme().'://'.$reqUrl->getHost().':'.$reqUrl->getPort(true));
        // command target contains only path/to/resource
        $reqUrl->setScheme(null)->setPort(null)->setHost(null); // TODO is it better way to do this?
        $command->setTarget( $reqUrl->toString() );

        $command->setHeaders($headers);
        $command->setBody($body);

        // let extra options received by Platform
        $command->setPlatformSettings($options);

        return $command;
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
        /*CommandRequestHttp([
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
            $args = array();
            (!isset($curr[0])) ?: $args['uri']     = $curr[0];
            (!isset($curr[1])) ?: $args['browser'] = $curr[1];
            (!isset($curr[2])) ?: $args['body']    = $curr[2];
            (!isset($curr[3])) ?: $args['headers'] = $curr[3];
        }

        return parent::__call($methodName, $args);
    }
}
