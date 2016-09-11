<?php
namespace Poirot\HttpAgent\Browser;

use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiMethod;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\Connection\Interfaces\iConnection;
use Poirot\Http\Interfaces\iHeader;
use Poirot\HttpAgent\Browser;
use Poirot\HttpAgent\Interfaces\iBrowserExpressionPlugin;
use Poirot\HttpAgent\Interfaces\iBrowserResponsePlugin;
use Poirot\HttpAgent\Interfaces\iHttpTransporter;
use Poirot\HttpAgent\CommandRequestHttp;
use Poirot\HttpAgent\Transporter\HttpSocketTransporter;


class HttpPlatform
    implements iPlatform
{
    /** @var Browser */
    protected $browser;
    /** @var iHttpTransporter */
    protected $_connection;

    /** @var BrowserPluginManager */
    protected $plugin_manager;
    /** @var PluginsInvokable */
    protected $_plugins;

    /**
     * Construct
     *
     * @param $browser
     */
    function __construct(Browser $browser)
    {
        $this->browser = $browser;
    }

    /**
     * Prepare Connection To Make Call
     *
     * - validate connection
     * - manipulate header or something in connection
     * - get connect to resource
     *
     * @param HttpSocketTransporter|iConnection $connection
     * @param iApiMethod|null                   $method
     *
     * @throws \Exception
     * @return HttpSocketTransporter|iHttpTransporter
     */
    function prepareTransporter(iConnection $connection, $method = null)
    {
        $BROWSER_OPTS = $this->browser->optsData();

        $reConnect = false;

        # check if we have something changed in connection options
        if ($BRW_ConOpts = $BROWSER_OPTS->getConnectionOptions())
            foreach($BRW_ConOpts->__props()->readable as $prop) {
                if (
                    $BRW_ConOpts->__isset($prop)
                    && ### option property must be set and available
                    ### not has new option or it may changed
                    (
                        !$connection->optsData()->__isset($prop)
                        || ( $connection->optsData()->__isset($prop)
                             && ### property exists but maybe with diffrent value
                            $connection->optsData()->__get($prop) !== $BRW_ConOpts->__get($prop)
                        )
                    )
                ) {
                    $connection->optsData()->__set($prop, $BRW_ConOpts->__get($prop));
                    $reConnect = true;
                }
            }

        # base url as connection server_url option
        ## made absolute server url from given baseUrl, but keep original untouched
        // http://raya-media/path/to/uri --> http://raya-media/
        $absServerUrl = new HttpUri();
        if ($BROWSER_OPTS->__isset('base_url')) {
            $absServerUrl->from($BROWSER_OPTS->getBaseUrl());
        }

        if (
            (!$connection->optsData()->__isset('server_url'))
            || (
                $connection->optsData()->__isset('server_url')
                &&
                $absServerUrl->toString() !== $connection->optsData()->getServerUrl()
            )
        ) {
            ($absServerUrl->getPath() === null) ?: $absServerUrl->getPath()->reset(); ### connect to host
            $connection->optsData()->setServerAddress($absServerUrl);
            $reConnect = true;
        }


        ## disconnect old connection to reconnect with newly options if has
        if ($connection->isConnected() && $reConnect)
            $connection->getConnect(); ## reconnect with new options

        $this->_connection = $connection; ## used on make expression/response
        return $connection;
    }

    /**
     * Build Platform Specific Expression To Send
     * Trough Connection
     *
     * @param iApiMethod|CommandRequestHttp $ReqMethod Method Interface
     *
     * @return HttpRequest
     */
    function makeExpression(iApiMethod $ReqMethod)
    {
        ## make a copy of browser when making changes on it by ReqMethod
        ### with bind browser options
        $CUR_BROWSER   = $this->browser;
        $this->browser = clone $CUR_BROWSER;


        if (!$ReqMethod instanceof CommandRequestHttp)
            $ReqMethod = new CommandRequestHttp($ReqMethod->toArray());


        # Request Options:
        ## (1)
        /*
         * $browser->POST('/api/v1/auth/login', [
         *      'form_data' => [
         *      // ...
         */
        if ($ReqMethod->getBrowserOptions()) {
            ## Browser specific options
            $prepConn = false;
            foreach($ReqMethod->getBrowserOptions()->props()->readable as $prop) {
                if ( $ReqMethod->getBrowserOptions()->__isset($prop) ) {
                    $this->browser->optsData()->__set($prop, $ReqMethod->getBrowserOptions()->__get($prop));
                    $prepConn = true;
                }
            }

            ## prepare connection again with new configs
            (!$prepConn) ?: $this->prepareTransporter($this->_connection, true);
        }

        // ...

        if ($ReqMethod->getUri() instanceof iHttpUri) {
            ## Reset Server Base Url When Absolute Http URI Requested
            /*
             * $browser->get(
             *   'http://www.pasargad-co.ir/forms/contact'
             *   , [ 'connection' => ['time_out' => 30],
             *     // ...
             */
            $t_uri = $ReqMethod->getUri();
            if ($t_uri->getHost()) {
                $this->browser->optsData()->setBaseUrl($t_uri);
                $this->prepareTransporter($this->_connection);
            }

            ### continue with sequence http uri
            $t_uri = ($ReqMethod->getUri()->getPath())
                ? $ReqMethod->getUri()->getPath()
                : new SeqPathJoinUri('/');

            $ReqMethod->setUri($t_uri);
        }

        # Build Request Http Message:
        ## (2)
        $REQUEST = $this->__getRequestObject();

        $REQUEST->setMethod($ReqMethod->getMethod());

        $serverUrl = $this->_connection->inOptions()->getServerUrl();
        $serverUrl = new HttpUri($serverUrl);
        $REQUEST->setHost($serverUrl->getHost());

        ## req Headers ------------------------------------------------------------------\
        ### default headers
        $reqHeaders = $REQUEST->getHeaders();

        if ($this->browser->optsData()->__isset('user_agent'))
            $reqHeaders->set(HeaderFactory::factory('User-Agent'
                , $this->browser->optsData()->getUserAgent()
            ));

        $reqHeaders->set(HeaderFactory::factory('Accept'
            , 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
        ));
        $reqHeaders->set(HeaderFactory::factory('Cache-Control'
            , 'no-cache'
        ));

        /*if ($this->browser->inOptions()->getConnection())
            (!$this->browser->inOptions()->getConnection()->isAllowDecoding())
                ?: $reqHeaders->set(HeaderFactory::factory('Accept-Encoding'
                , 'gzip, deflate, sdch'
            ));*/

        ### headers as default browser defined header
        foreach($this->browser->optsData()->getRequestOptions()->getHeaders() as $h)
            $reqHeaders->set($h);

        ### headers as request method options
        if ($ReqMethod->getHeaders()) {
            /** @var iHeader $h */
            foreach($ReqMethod->getHeaders() as $h) {
                if ($reqHeaders->has($h->getLabel()))
                    $reqHeaders = $reqHeaders->del($h->getLabel());

                $reqHeaders->set($h);
            }
        }

        ## req Uri ----------------------------------------------------------------------\
        $basePath = null;
        if ($this->browser->optsData()->__isset('base_url'))
            $basePath   = $this->browser->optsData()->getBaseUrl()->getPath();

        if($basePath === null)
            $basePath = new SeqPathJoinUri('/');

        $targetUri = $basePath->merge($ReqMethod->getUri()); ### merge with request base url path
        $REQUEST->getUri()->setPath($targetUri);

        ### remove unnecessary iHttpUri parts such as port, host, ...
        ### its presented as host and etc. on Request Message Object
        $uri = new HttpUri([
            'path'     => $REQUEST->getUri()->getPath(),
            'query'    => $REQUEST->getUri()->getQuery(),
            'fragment' => $REQUEST->getUri()->getFragment(),
        ]);
        $REQUEST->setUri($uri);

        ## req body ---------------------------------------------------------------------\
        $REQUEST->setBody($ReqMethod->getBody());


        # Implement Browser Plugins:
        ## (3)
        foreach($ReqMethod->getBrowserOptions()->props()->readable as $prop) {
            if (!$this->getPluginManager()->has($prop))
                /*
                 * $browser->POST('/api/v1/auth/login', [
                 *      'form_data' => [ // <=== plugin form_data will trigger with this params
                 *      // ...
                */
                continue; ## no plugin bind on this option

            /** @var Browser\Plugin\BaseBrowserPlugin $plugin */
            $plugin = $this->getPluginManager()->fresh($prop);
            $plugin->from($ReqMethod->getBrowserOptions()->__get($prop)); ### options for service

            if($plugin instanceof iBrowserExpressionPlugin)
                $plugin->withHttpRequest($REQUEST);
        }

        $this->browser = $CUR_BROWSER;
        return $REQUEST;
    }

    /**
     * Build Response Object From Server Result
     *
     * - Result must be compatible with platform
     * - Throw exceptions if response has error
     *
     * @param HttpResponse $response Server Result
     *
     * @throws \Exception
     * @return iResponse
     */
    function makeResponse($response)
    {
        foreach ($this->getPluginManager()->listServices() as $serviceName) {
            if (($service = $this->getPluginManager()->get($serviceName)) instanceof iBrowserResponsePlugin)
                $service->withHttpResponse($response);
        }

        $result = new ResponsePlatform($response);
        return $result;
    }


    // ...

    protected function __getRequestObject()
    {
        $request = new HttpRequest;

        if ($reqOptions = $this->browser->optsData()->getRequestOptions())
            ## build with browser request options if has
            $request->from($reqOptions);

        return $request;
    }


    // Plugins:

    /**
     * Plugin Manager
     *
     * @return PluginsInvokable
     */
    function plg()
    {
        if (!$this->_plugins)
            $this->_plugins = new PluginsInvokable(
                $this->getPluginManager()
            );

        return $this->_plugins;
    }

    /**
     * Get Plugins Manager
     *
     * @return BrowserPluginManager|AbstractPlugins
     */
    function getPluginManager()
    {
        if (!$this->plugin_manager)
            $this->setPluginManager(new BrowserPluginManager);

        return $this->plugin_manager;
    }

    /**
     * Set Plugins Manager
     *
     * @param BrowserPluginManager|AbstractPlugins $plugins
     *
     * @return $this
     */
    function setPluginManager(AbstractPlugins $plugins)
    {
        $this->plugin_manager = $plugins;
        return $this;
    }
}
