<?php
namespace Poirot\HttpAgent\Browser;

use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\ApiClient\Interfaces\Request\iApiMethod;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\Http\Header\HeaderFactory;
use Poirot\Http\Message\HttpRequest;
use Poirot\Http\Message\HttpResponse;
use Poirot\HttpAgent\Browser;
use Poirot\HttpAgent\Interfaces\iHttpTransporter;
use Poirot\HttpAgent\ReqMethod;
use Poirot\HttpAgent\Transporter\StreamHttpTransporter;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\SeqPathJoinUri;

class HttpPlatform implements iPlatform
{
    /** @var Browser */
    protected $browser;
    /** @var iHttpTransporter */
    protected $_connection;

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
     * @param StreamHttpTransporter|iConnection $connection
     *
     * @throws \Exception
     * @return StreamHttpTransporter|iHttpTransporter
     */
    function prepareConnection(iConnection $connection, $debug = false)
    {
        $BROWSER_OPTS = $this->browser->inOptions();

        $reConnect = false;

        # check if we have something changed in connection options
        if ($conOptions = $BROWSER_OPTS->getConnection())
            foreach($conOptions->props()->readable as $prop) {
                if (
                    ## not has new option or it may changed
                    !$connection->inOptions()->__isset($prop)
                    || ($connection->inOptions()->__get($prop) !== ($val = $conOptions->__get($prop))) && $val !== null
                ) {
                    $connection->inOptions()->__set($prop, $conOptions->__get($prop));
                    $reConnect = true;
                }
            }

        # base url as connection server_url option
        // http://raya-media/path/to/uri --> http://raya-media/
        $absServerUrl = clone $this->browser->inOptions()->getBaseUrl();
        ## made absolute server url from given baseUrl, but keep original untouched
        if ($absServerUrl->getPath())
            $absServerUrl->getPath()->reset();

        if ($absServerUrl->toString() !== $connection->inOptions()->getServerUrl()) {
            $connection->inOptions()->setServerUrl($absServerUrl);
            $reConnect = true;
        }


        ## disconnect old connection to reconnect with newly options if has
        if ($connection->isConnected() && $reConnect)
            $connection->close();

        $this->_connection = $connection; ## used on make expression/response
        return $connection;
    }

    /**
     * Build Platform Specific Expression To Send
     * Trough Connection
     *
     * @param iApiMethod|ReqMethod $method Method Interface
     *
     * @return HttpRequest
     */
    function makeExpression(iApiMethod $method)
    {
        ## make a copy of browser when making changes on it by ReqMethod
        ### with bind browser options
        $CUR_BROWSER = $this->browser;
        $this->browser = clone $CUR_BROWSER;

        if (!$method instanceof ReqMethod)
            $method = new ReqMethod($method->toArray());

        $method_args = $method->toArray();


        if ($method_args['browser']) {
            ### Browser specific options
            $prepConn = false;
            foreach($method_args['browser']->props()->readable as $prop) {
                if ($val = $method_args['browser']->__get($prop)) {
                    $this->browser->inOptions()->__set($prop, $val);
                    $prepConn = true;
                }
            }

            ## prepare connection again with new configs
            (!$prepConn) ?: $this->prepareConnection($this->_connection, true);
        }

        ## req Uri
        if ($method_args['uri'] instanceof iHttpUri) {
            ### reset server_url
            $this->browser->inOptions()->setBaseUrl($method_args['uri']);
            $this->prepareConnection($this->_connection);

            ### continue with sequence http uri
            $method_args['uri'] = ($method_args['uri']->getPath()) ? $method_args['uri']->getPath() : new SeqPathJoinUri('/');
        }

        // ...

        # Build Request:
        $request = new HttpRequest;

        $request->setMethod($method_args['method']);
        $request->setHost($this->_connection->inOptions()->getServerUrl()->getHost());

        ## req Headers ------------------------------------------------------------------\
        ### default headers
        $reqHeaders = $request->getHeaders();
        $reqHeaders->set(HeaderFactory::factory('User-Agent'
            , $this->browser->inOptions()->getUserAgent()
        ));
        $reqHeaders->set(HeaderFactory::factory('Accept'
            , 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
        ));

        if ($this->browser->inOptions()->getConnection())
            (!$this->browser->inOptions()->getConnection()->isAllowDecoding())
                ?: $reqHeaders->set(HeaderFactory::factory('Accept-Encoding'
                , 'gzip, deflate, sdch'
            ));

        ### headers as request method options
        if (is_array($method_args['headers']))
            foreach($method_args['headers'] as $h)
                $reqHeaders->set($h);

        ## req Uri ----------------------------------------------------------------------\
        $baseUrl   = $this->browser->inOptions()->getBaseUrl()->getPath();
        if (!$baseUrl)
            $baseUrl = new SeqPathJoinUri('/');
        $targetUri = $baseUrl->merge($method_args['uri']);

        $request->setUri($targetUri);

        ## req body ---------------------------------------------------------------------\
        $request->setBody($method_args['body']);


        $this->browser = $CUR_BROWSER;
        return $request;
    }

    /**
     * Build Response Object From Server Result
     *
     * - Result must be compatible with platform
     * - Throw exceptions if response has error
     *
     * @param HttpResponse $result Server Result
     *
     * @throws \Exception
     * @return iResponse
     */
    function makeResponse($result)
    {
        return new ResponsePlatform($result);
    }
}
