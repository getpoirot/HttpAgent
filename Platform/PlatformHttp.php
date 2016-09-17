<?php
namespace Poirot\HttpAgent\Platform;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;

use Poirot\Connection\Interfaces\iConnection;

use Poirot\Http\Header\FactoryHttpHeader;
use Poirot\Http\HttpRequest;

use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Http\Interfaces\iHttpResponse;

use Poirot\HttpAgent\Browser;
use Poirot\HttpAgent\Browser\DataOptionsBrowser;
use Poirot\HttpAgent\Interfaces\iPluginBrowserExpression;
use Poirot\HttpAgent\Interfaces\iPluginBrowserResponse;
use Poirot\HttpAgent\Interfaces\iTransporterHttp;
use Poirot\HttpAgent\CommandRequestHttp;
use Poirot\HttpAgent\Transporter\TransporterHttpSocket;

use Poirot\Std\ConfigurableSetter;
use Poirot\Std\Interfaces\Pact\ipOptionsProvider;


class PlatformHttp
    implements iPlatform
    , ipOptionsProvider
{
    /** @var TransporterHttpSocket */
    protected $_connection;

    /** @var DataOptionsBrowser */
    protected $options;
    /** @var PluginsOfBrowser */
    protected $plugins;
    
    protected $_availablePlugins = array();


    /**
     * Construct
     *
     * - construct([
     *    'base_url'            => 'http://google.com'
     *    'connection_options'  => ['time_out' => 20]
     * ]);
     *
     * @param array|\Traversable|null $options Options when using as base_url
     */
    function __construct($options = null)
    {
        $this->optsData()->import($options);
    }

    /**
     * Prepare Connection To Make Call
     *
     * - validate connection
     * - manipulate header or something in connection
     * - get connect to resource
     *
     * @param TransporterHttpSocket|iConnection $transporterHttp
     * @param iApiCommand|null                  $command
     *
     * @throws \Exception
     * @return TransporterHttpSocket|iTransporterHttp
     */
    function prepareTransporter(iConnection $transporterHttp, $command = null)
    {
        if (!$transporterHttp instanceof TransporterHttpSocket)
            throw new \Exception;
        
        $reConnect = false;

        # check if we have something changed in connection options:
        foreach($givenConnectionOptions = $this->optsData()->getConnectionOptions() as $prop => $value)
        {
            if ($value === null)
                // Value is Empty; Nothing To Do!!!
                continue;

            $currConnectionOption  = $transporterHttp->optsData()->__get($prop);
            if ($currConnectionOption !== $value) {
                // property exists but maybe with different value
                $transporterHttp->optsData()->__set($prop, $value);
                $reConnect = true;
            }
        }
        
        
        # provide "server_address" connection options from "base_url" browser option:
        // made absolute server url from given baseUrl, but keep original untouched
        // http://raya-media/path/to/uri --> http://raya-media/
        $baseUrl = $this->optsData()->getBaseUrl();
        if (false !== $baseUrl = parse_url($baseUrl)) 
        {
            if ( isset($baseUrl['scheme']) && isset($baseUrl['host']) ) {
                // Connect To HOST
                $serverHost = '';
                (!isset($baseUrl['scheme'])) ?: $serverHost .= $baseUrl['scheme'].'://';
                $serverHost .= $baseUrl['host'];
                (!isset($baseUrl['port']))   ?: $serverHost .= ':'.$baseUrl['port'];
                
                if ($serverHost !== $transporterHttp->optsData()->getServerAddress()) {
                    $transporterHttp->optsData()->setServerAddress($serverHost);
                    $reConnect = true;
                }
            }
        }

        
        ## disconnect old connection to reconnect with newly options if has
        if ($transporterHttp->isConnected() && $reConnect)
            $transporterHttp->getConnect(); ## reconnect with new options

        $this->_connection = $transporterHttp; ## used on make expression/response
        return $transporterHttp;
    }

    /**
     * Build Platform Specific Expression To Send
     * Trough Connection
     *
     * @param iApiCommand|CommandRequestHttp $CommandRequest Method Interface
     *
     * @return iHttpRequest
     */
    function makeExpression(iApiCommand $CommandRequest)
    {
        if (!$CommandRequest instanceof CommandRequestHttp)
            $CommandRequest = new CommandRequestHttp($CommandRequest->getArguments());


        # Request Options:
        ## (1)
        /*
         * $browser->POST('/api/v1/auth/login', [
         *      'form_data' => [
         *      // ...
         */
        if ($CommandRequest->getBrowserOptions()) {
            ## Browser specific options
            $reConnect = false;
            foreach($CommandRequest->getBrowserOptions() as $prop => $value) 
            {
                if ($this->optsData()->__get($prop) !== $value) {
                    // Something changes in options; it may affect connection !!
                    $this->optsData()->__set($prop, $value);
                    $reConnect = true;
                }
            }

            ## prepare connection again with new configs
            if ($reConnect)
                $this->prepareTransporter($this->_connection);
        }

        
        // ...

        if ($uri = $CommandRequest->getUri()) {
            ## Reset Server Base Url When Absolute Http URI Requested
            /*
             * $browser->get(
             *   'http://www.pasargad-co.ir/forms/contact'
             *   , [ 'connection' => ['time_out' => 30],
             *     // ...
             */
            $parsedUri = parse_url($uri);
            if (isset($parsedUri['host'])) {
                // Reconnect to host if changes
                $this->optsData()->setBaseUrl($uri);
                $this->prepareTransporter($this->_connection);
            }
        }

        # Build Request Http Message:
        ## (2)
        $RequestHttp = $this->_newHttpRequest();
        
        ## request method
        $RequestHttp->setMethod($CommandRequest->getMethod());
        
        ## request host
        $serverUrl = $this->_connection->optsData()->getServerAddress();
        if ($host = parse_url($serverUrl, PHP_URL_HOST)) {
            if ($port = parse_url($serverUrl, PHP_URL_PORT))
                $host = "$host:$port";
            
            $RequestHttp->setHost($host);
        }
        
        ## request headers
        
        // default headers
        $reqHeaders = $RequestHttp->headers();
        
        $reqHeaders->insert(FactoryHttpHeader::of(array(
            'User-Agent' => $this->optsData()->getUserAgent()
        )));

        $reqHeaders->insert(FactoryHttpHeader::of(array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        )));

        $reqHeaders->insert(FactoryHttpHeader::of(array(
            'Cache-Control' => 'no-cache',
        )));
        
        // headers as default browser defined header
        if ($headers = $this->optsData()->getRequestOptions()->getHeaders())
            $RequestHttp->setHeaders($headers);

        // headers as request method options
        if ($headers = $CommandRequest->getHeaders())
            $RequestHttp->setHeaders($headers);

        
        ## request uri
        
        if (null === $baseUrl = $this->optsData()->getBaseUrl())
            $baseUrl = '/';

        
        $targetUri = $CommandRequest->getUri();
        $targetUri = rtrim($baseUrl, '/').$targetUri;
        $RequestHttp->setTarget($targetUri);
        
        ## request body
        $RequestHttp->setBody($CommandRequest->getBody());


        # Implement Browser Plugins:
        ## (3)
        foreach($CommandRequest->getBrowserOptions() as $prop => $value) 
        {
            if (!$this->getPlugins()->has($prop))
                /*
                 * $browser->POST('/api/v1/auth/login', [
                 *      'form_data' => [ // <=== plugin form_data will trigger with this params
                 *      // ...
                */
                continue; ## no plugin bind on this option

            /** @var Browser\Plugin\BaseBrowserPlugin $plugin */
            // Make Fresh With Options So Later Can Get; see makeResponse()
            $plugin = $this->getPlugins()->fresh($prop, $value);
            $this->_availablePlugins[] = $prop; 

            if($plugin instanceof iPluginBrowserExpression)
                $RequestHttp = $plugin->withHttpRequest($RequestHttp);
        }

        return $RequestHttp;
    }

    /**
     * Build Response Object From Server Result
     *
     * - Result must be compatible with platform
     * - Throw exceptions if response has error
     *
     * @param iHttpResponse $response Server Result
     *
     * @throws \Exception
     * @return iResponse
     */
    function makeResponse($response)
    {
        foreach ($this->_availablePlugins as $pluginName) {
            $service = $this->getPlugins()->get($pluginName);
            if ($service instanceof iPluginBrowserResponse)
                $response = $service->withHttpResponse($response);
        }

        $result = new ResponsePlatform($response);
        return $result;
    }

    
    // ..

    /**
     * Set Plugins Manager
     * 
     * @param PluginsOfBrowser $plugins
     * 
     * @return $this
     */
    function setPlugins(PluginsOfBrowser $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }
    
    function getPlugins()
    {
        if (!$this->plugins) {
            $this->plugins = new PluginsOfBrowser($this->optsData()->getPluginsOptions());
        }
        
        return $this->plugins;
    }

    // Implement OptionsProviderInterface

    /**
     * @return DataOptionsBrowser
     */
    function optsData()
    {
        if (!$this->options)
            $this->options = static::newOptsData();

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
     * @param null|mixed $builder Builder Options as Constructor
     *
     * @return DataOptionsBrowser
     */
    static function newOptsData($builder = null)
    {
        return new DataOptionsBrowser($builder);
    }


    // ...

    protected function _newHttpRequest()
    {
        $request = new HttpRequest;

        if ($reqOptions = $this->optsData()->getRequestOptions())
            ## build with browser request options if has
            $request->with($request::parseWith($reqOptions));

        return $request;
    }
}
