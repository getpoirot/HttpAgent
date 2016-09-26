<?php
namespace Poirot\HttpAgent\Platform;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiCommand;
use Poirot\ApiClient\Interfaces\Response\iResponse;

use Poirot\Connection\Exception\ConnectException;
use Poirot\Connection\Interfaces\iConnection;

use Poirot\Ioc\Container\BuildContainer;

use Poirot\Psr7\HttpRequest;
use Poirot\Psr7\HttpResponse;
use Poirot\Psr7\Uri;

use Poirot\Std\ConfigurableSetter;
use Poirot\Std\Interfaces\Pact\ipOptionsProvider;
use Poirot\Std\Struct\DataOptionsOpen;

use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Psr\StreamBridgeInPsr;
use Poirot\Stream\Streamable\SLimitSegment;

use Poirot\HttpAgent\Browser\Plugin\BaseBrowserPlugin;
use Poirot\HttpAgent\Interfaces\iPluginBrowserExpression;
use Poirot\HttpAgent\Interfaces\iPluginBrowserResponse;
use Poirot\HttpAgent\Interfaces\iTransporterHttp;
use Poirot\HttpAgent\CommandRequestHttp;
use Poirot\HttpAgent\Transporter\TransporterHttpSocket;


class PlatformHttp
    extends ConfigurableSetter
    implements iPlatform
{
    /** @var CommandRequestHttp */
    protected $command;

    /** @var TransporterHttpSocket|iConnection*/
    protected $transporter;
    /** @var PluginsOfBrowser */
    protected $plugins;
    /** @var DataOptionsOpen */
    protected $pluginOptions;

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
            'transporter_setting',
            'plugin_manager',
            'plugin_manager_setting',
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
            // build platform with given command settings
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

        $transporter = $this->_prepareTransporter($command);

        # Build Request Http Message:
        /** @var RequestInterface $request */
        $request = $this->_makeExpressionRequest($command);

        ## Finalize Request with Plugins:

        $AvailablePlugins = array();

        foreach($this->getPluginOptions() as $prop => $value)
        {
            if (!$this->pluginManager()->has($prop))
                /*
                 * $browser->POST('/api/v1/auth/login', [ 'plugin' => [
                 *      'form_data' => [ // <=== plugin form_data will trigger with this params
                 *      // ...
                */
                continue; ## no plugin bind on this option

            /** @var BaseBrowserPlugin $plugin */
            // Make Fresh With Options So Later Can Get; see makeResponse()
            $plugin = $this->pluginManager()->fresh($prop, $value);
            $AvailablePlugins[] = $prop;

            if($plugin instanceof iPluginBrowserExpression) {
                /** @var iPluginBrowserExpression $plugin */
                $r = $plugin->withHttpRequest($request);
                if ($r && $r instanceof RequestInterface) {
                    $request = $r;
                    continue;
                }

                throw new \Exception(sprintf(
                    'Invalid Response Provided by (%s); give back: (%s).'
                    , \Poirot\Std\flatten($plugin) , $r
                ));
            }
        }


        # Send Request Over Wire

        /** @var iStreamable $response */
        $response = $transporter->send($request);

        
        # Make Response
        $rHeaders = \Poirot\Connection\Http\readAndSkipHeaders($response);
        $rHeaders = \Poirot\Connection\Http\parseResponseHeaders($rHeaders);
        $body     = new SLimitSegment($response, -1, $response->getCurrOffset()); // limit body from current offset to end stream

        /** @var HttpResponse $response */
        $response = new HttpResponse(new StreamBridgeInPsr($body), $rHeaders['status'], $rHeaders['headers']);


        # Finalize Response with Plugins
        foreach ($AvailablePlugins as $pluginName) {
            $service = $this->pluginManager()->get($pluginName);
            if ($service instanceof iPluginBrowserResponse) {
                $r = $service->withHttpResponse($response);
                if ($r && $r instanceof ResponseInterface) {
                    $response = $r;
                    continue;
                }

                throw new \Exception(sprintf(
                    'Invalid Response Provided by (%s); give back: (%s).'
                    , \Poirot\Std\flatten($service) , $r
                ));
            }
        }

        $result = new ResponsePlatform($response);
        return $result;
    }
    
    
    // Options:

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
    function setTransporterSetting($options)
    {
        // Settings directly take affect on transporter

        $transporter = $this->transporter();
        if (!$transporter instanceof ipOptionsProvider)
            throw new \Exception(sprintf(
                'Transporter (%s) is not configurable.'
                , \Poirot\Std\flatten($transporter)
            ));

        $transporter->optsData($options);
        return $this;
    }

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
     * Set Plugins Manager
     * 
     * @param PluginsOfBrowser $plugins
     * 
     * @return $this
     */
    function setPluginManager(PluginsOfBrowser $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * Set Plugins Builder Settings
     *
     * @param mixed $options
     *
     * @return $this
     * @throws \Exception
     */
    function setPluginManagerSetting($options)
    {
        // Settings directly take affect on plugin manager

        $builder = new BuildContainer($options);
        $builder->build(
            $this->pluginManager()
        );

        return $this;
    }
    
    function pluginManager()
    {
        if (!$this->plugins)
            $this->plugins = new PluginsOfBrowser;

        
        return $this->plugins;
    }

    /**
     * Set Plugin Options
     *
     * ['plugin' =>
     *    'form_data' => [
     *       'username' => 'Payam',
     *       ...
     *    ]
     * ]
     *
     * @param array|\Traversable $options
     *
     * @return $this
     */
    function setPlugin($options)
    {
        $this->getPluginOptions()->import($options);
        return $this;
    }

    /**
     * Get Plugin Options
     *
     * @return DataOptionsOpen
     */
    function getPluginOptions()
    {
        if (!$this->pluginOptions)
            $this->pluginOptions = new DataOptionsOpen;

        return $this->pluginOptions;
    }
    
    
    // ...

    /**
     * Prepare Connection To Make Call
     *
     * - validate connection
     * - manipulate header or something in connection
     * - get connect to resource
     *
     * @param CommandRequestHttp $command
     *
     * @return iTransporterHttp|TransporterHttpSocket
     * @throws \Exception
     */
    protected function _prepareTransporter(CommandRequestHttp $command)
    {
        $transporter = $this->transporter();

        if (!$transporter instanceof TransporterHttpSocket)
            throw new \Exception(sprintf(
                'Transporter (%s) not supported.'
                , \Poirot\Std\flatten($transporter)
            ));

        // check transporter server address been same as command host
        if ($transporter->optsData()->getServerAddress() !== $command->getHost()) {
            $transporter->optsData()->setServerAddress($command->getHost());
            $transporter->close(); // reconnect
        }

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

    /**
     * Make Request Expression From Command
     *
     * @param CommandRequestHttp $command
     *
     * @return RequestInterface
     * @throws \Exception
     */
    protected function _makeExpressionRequest(CommandRequestHttp $command)
    {
        $uri = new Uri($command->getHost().'/'.ltrim($command->getTarget(), '/'));

        $httpRequest = new HttpRequest();

        ## request method
        $httpRequest = $httpRequest
            ->withMethod($command->getMethod())
            ## request uri
            ->withUri($uri)
        ;

        ## request body
        if ($body = $command->getBody())
            $httpRequest = $httpRequest->withBody($body);


        ## request headers

        // default headers
        $httpRequest = $this->_exprAddDefaultHeadersTo($httpRequest);

        // headers as request method options
        if ($headers = $command->getHeaders()) {
            foreach ($headers as $name => $val)
                $httpRequest = $httpRequest->withHeader($name, $val);
        }

        return $httpRequest;
    }

    /**
     * Attach Default Headers
     * @param HttpRequest $httpRequest
     * @return HttpRequest
     */
    private function _exprAddDefaultHeadersTo($httpRequest)
    {
        $httpRequest = $httpRequest
            // TODO User-Agent name
            ->withHeader('User-Agent', 'xxxxxxx')
            ->withHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8')
            ->withHeader('Cache-Control', 'no-cache')
        ;

        return $httpRequest;
    }

    // ..

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
