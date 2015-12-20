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
       $this->browser = clone $browser;
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
    function prepareConnection(iConnection $connection)
    {
        $brwOptions = $this->browser->inOptions();

        $reConnect = false;

        # check if we have something changed in connection options
        if ($conOptions = $brwOptions->getConnection())
            foreach($conOptions->props()->readable as $prop) {
                if (
                    ## not has new option or it may changed
                    !$connection->inOptions()->__isset($prop)
                    || ($connection->inOptions()->__get($prop) !== $conOptions->__get($prop))
                ) {
                    $connection->inOptions()->__set($prop, $conOptions->__get($prop));
                    $reConnect = true;
                }
            }

        # base url as connection server_url option
        // http://raya-media/path/to/uri --> http://raya-media/
        $absServerUrl = clone $this->browser->inOptions()->getBaseUrl();
        ## made absolute server url from given baseUrl
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
     * @param iApiMethod $method Method Interface
     *
     * @return HttpRequest
     */
    function makeExpression(iApiMethod $method)
    {
        $args = $method->getArguments();

        ## req Uri
        if ($args['uri'] instanceof iHttpUri) {
            ### reset server_url
            $this->browser->inOptions()->setBaseUrl($args['uri']);
            $this->prepareConnection($this->_connection);

            ### continue with sequence http uri
            $args['uri'] = ($args['uri']->getPath()) ? $args['uri']->getPath() : new SeqPathJoinUri('/');
        }

        // ...

        # Build Request:
        $request = new HttpRequest;
        $request->setMethod($args['method']);
        $request->setHost($this->_connection->inOptions()->getServerUrl()->getHost());

        ## req Headers
        ### default headers
        $reqHeaders = $request->getHeaders();
        $reqHeaders->set(HeaderFactory::factory('User-Agent'
            , $this->browser->inOptions()->getUserAgent()
        ));
        $reqHeaders->set(HeaderFactory::factory('Accept'
            , 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
        ));
        (!$this->browser->inOptions()->getConnection()->isAllowDecoding())
            ?: $reqHeaders->set(HeaderFactory::factory('Accept-Encoding'
            , 'gzip, deflate, sdch'
        ));
        ### headers as request method options
        if (is_array($args['headers']))
            foreach($args['headers'] as $h)
                $reqHeaders->set($h);

        ## req Uri
        $baseUrl   = $this->browser->inOptions()->getBaseUrl()->getPath();
        if (!$baseUrl)
            $baseUrl = new SeqPathJoinUri('/');
        $targetUri = $baseUrl->merge($args['uri']);

        $request->setUri($targetUri);

        ## req body
        $request->setBody($args['body']);

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
