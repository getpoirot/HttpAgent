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
     * @param iApiMethod|ReqMethod $ReqMethod Method Interface
     *
     * @return HttpRequest
     */
    function makeExpression(iApiMethod $ReqMethod)
    {
        ## make a copy of browser when making changes on it by ReqMethod
        ### with bind browser options
        $CUR_BROWSER = $this->browser;
        $this->browser = clone $CUR_BROWSER;

        if (!$ReqMethod instanceof ReqMethod)
            $ReqMethod = new ReqMethod($ReqMethod->toArray());

        if ($ReqMethod->getBrowser()) {
            ### Browser specific options
            $prepConn = false;
            foreach($ReqMethod->getBrowser()->props()->readable as $prop) {
                if ($val = $ReqMethod->getBrowser()->__get($prop)) {
                    $this->browser->inOptions()->__set($prop, $val);
                    $prepConn = true;
                }
            }

            ## prepare connection again with new configs
            (!$prepConn) ?: $this->prepareConnection($this->_connection, true);
        }

        ## req Uri
        if ($ReqMethod->getUri() instanceof iHttpUri) {
            ### reset server_url
            $this->browser->inOptions()->setBaseUrl($ReqMethod->getUri());
            $this->prepareConnection($this->_connection);

            ### continue with sequence http uri
            $t_uri = ($ReqMethod->getUri()->getPath())
                ? $ReqMethod->getUri()->getPath()
                : new SeqPathJoinUri('/');

            $ReqMethod->setUri($t_uri);
        }

        // ...

        # Build Request:
        $REQUEST = $this->__getRequestObject();

        $REQUEST->setMethod($ReqMethod->getMethod());
        $REQUEST->setHost($this->_connection->inOptions()->getServerUrl()->getHost());

        ## req Headers ------------------------------------------------------------------\
        ### default headers
        $reqHeaders = $REQUEST->getHeaders();
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
        if ($ReqMethod->getHeaders()) {
            foreach($ReqMethod->getHeaders() as $h)
                $reqHeaders->set($h);
        }

        ## req Uri ----------------------------------------------------------------------\
        $baseUrl   = $this->browser->inOptions()->getBaseUrl()->getPath();
        if (!$baseUrl)
            $baseUrl = new SeqPathJoinUri('/');
        $targetUri = $baseUrl->merge($ReqMethod->getUri());

        $REQUEST->setUri($targetUri);

        ## req body ---------------------------------------------------------------------\
        $REQUEST->setBody($ReqMethod->getBody());


        $this->browser = $CUR_BROWSER;
        return $REQUEST;
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


    // ...

    protected function __getRequestObject()
    {
        $request = new HttpRequest;

        if ($reqOptions = $this->browser->inOptions()->getRequest())
            ## build with browser request options if has
            $request->from($reqOptions);

        return $request;
    }
}
