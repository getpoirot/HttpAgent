<?php
namespace Poirot\HttpAgent\Platform;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;

use Poirot\Connection\Exception\ConnectException;
use Poirot\Connection\Interfaces\iConnection;

use Poirot\Http\Header\FactoryHttpHeader;
use Poirot\Http\HttpRequest;

use Poirot\HttpAgent\Browser;
use Poirot\HttpAgent\Browser\DataOptionsPlatform;
use Poirot\HttpAgent\Interfaces\iPluginBrowserExpression;
use Poirot\HttpAgent\Interfaces\iPluginBrowserResponse;
use Poirot\HttpAgent\Interfaces\iTransporterHttp;
use Poirot\HttpAgent\CommandRequestHttp;
use Poirot\HttpAgent\Transporter\TransporterHttpSocket;

use Poirot\Std\ConfigurableSetter;
use Poirot\Std\Interfaces\Pact\ipConfigurable;


class PlatformHttp
    extends ConfigurableSetter
    implements iPlatform
{
    /** @var CommandRequestHttp */
    protected $command;

    /** @var TransporterHttpSocket|iConnection*/
    protected $transporter;

    /** @var DataOptionsPlatform */
    protected $options;
    /** @var PluginsOfBrowser */
    protected $plugins;
    
    protected $_availablePlugins = array();


    /**
     * Construct
     *
     * @param array|\Traversable $options
     */
    function __construct($options = null)
    {
        $this->putBuildPriority(array(
            'transporter',
            'transporter_settings',
        ));
        
        parent::__construct($options);
    }
    
    /**
     * Build Platform Specific Expression To Send Trough Transporter
     *
     * @param iApiCommand|CommandRequestHttp $command Method Interface
     *
     * @return iPlatform Self or Copy/Clone
     */
    function withCommand(iApiCommand $command)
    {
        if (!$command instanceof CommandRequestHttp)
            $command = new CommandRequestHttp($command->getArguments());
        
        /** @var CommandRequestHttp $command */

        $platform = clone $this;
        
        $settings = $command->getPlatformSettings();
        if ($settings)
            $platform->with($platform::parseWith($settings));
        
        $platform->command = $command;
        return $platform;
    }

    /**
     * Build Response with send Expression over Transporter
     *
     * - Result must be compatible with platform
     * - Throw exceptions if response has error
     *
     * @throws \Exception Command Not Set
     * @return iResponse
     */
    function send()
    {
        if (!$command = $this->command)
            throw new \Exception('Command Not Provided Yet! try ::withCommand(iApiCommand) method.');

        $transporter = $this->transporter();
        $transporter = $this->_prepareTransporter($transporter);

        

        # Request Options:
        ## (1)
        /*
         * $browser->POST('/api/v1/auth/login', [
         *      'form_data' => [
         *      // ...
         */
        if ($command->getPlatformSettings()) {
            ## Browser specific options
            $reConnect = false;
            foreach($command->getPlatformSettings() as $prop => $value)
            {
                if ($this->optsData()->__get($prop) !== $value) {
                    // Something changes in options; it may affect connection !!
                    $this->optsData()->__set($prop, $value);
                    $reConnect = true;
                }
            }

            ## prepare connection again with new configs
            if ($reConnect)
                $this->_prepareTransporter($this->_connection);
        }


        // ...

        if ($uri = $command->getTarget()) {
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
                $this->_prepareTransporter($this->_connection);
            }
        }

        # Build Request Http Message:
        ## (2)
        $RequestHttp = $this->_newHttpRequest();

        ## request method
        $RequestHttp->setMethod($command->getMethod());

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
        if ($headers = $command->getHeaders())
            $RequestHttp->setHeaders($headers);


        ## request uri

        if (null === $baseUrl = $this->optsData()->getBaseUrl())
            $baseUrl = '/';


        $targetUri = $command->getTarget();
        $targetUri = rtrim($baseUrl, '/').$targetUri;
        $RequestHttp->setTarget($targetUri);

        ## request body
        $RequestHttp->setBody($command->getBody());


        # Implement Browser Plugins:
        ## (3)
        foreach($command->getPlatformSettings() as $prop => $value)
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
        
        
        
        
        
        foreach ($this->_availablePlugins as $pluginName) {
            $service = $this->getPlugins()->get($pluginName);
            if ($service instanceof iPluginBrowserResponse)
                $transporter = $service->withHttpResponse($transporter);
        }

        $result = new ResponsePlatform($transporter);
        return $result;
    }
    
    
    // Options:

    /**
     * Get Connection Adapter
     *
     * @return TransporterHttpSocket
     */
    function transporter()
    {
        if (!$this->transporter)
            $this->transporter = new TransporterHttpSocket;

        return $this->transporter;
    }

    /**
     * Set Transporter
     *
     * @param iConnection $transporter
     *
     * @return $this
     */
    function setTransporter(iConnection $transporter)
    {
        $this->transporter = $transporter;
        return $this;
    }

    /**
     * Set Transporter Options
     *
     * @param mixed $options
     *
     * @return $this
     * @throws \Exception
     */
    function setTransporterSettings($options)
    {
        $transporter = $this->transporter();
        if (!$transporter instanceof ipConfigurable)
            throw new \Exception(sprintf(
                'Transporter (%s) is not configurable.'
            ));

        $transporter->close();
        $transporter->with($transporter::parseWith($options));
        return $this;
    }
    
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
    
    
    // ...

    /**
     * Prepare Connection To Make Call
     *
     * - validate connection
     * - manipulate header or something in connection
     * - get connect to resource
     *
     * @param TransporterHttpSocket|iConnection $transporter
     *
     * @throws \Exception
     * @return TransporterHttpSocket|iTransporterHttp
     */
    protected function _prepareTransporter(iConnection $transporter)
    {
        if (!$transporter instanceof TransporterHttpSocket)
            throw new \Exception;
        
        try {
            if (!$transporter->isConnected())
                $transporter->getConnect();
        } catch (\Exception $e) {
            throw new ConnectException(sprintf(
                'Error While Connecting To Transporter'
            ), $e->getCode(), $e);
        }
        
        return $transporter;
    }
    
    function __clone()
    {
        $_f__clone_array = function($arr) use (&$_f__clone_array) {
            foreach ($arr as &$v) {
                if (is_array($v))
                    $_f__clone_array($v);
                elseif (is_object($v))
                    $v = clone $v;
            }
        };

        foreach($this as &$val) {
            if (is_array($val))
                $_f__clone_array($val);
            elseif (is_object($val))
                $val = clone $val;
        }
    }
}
