<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\Interfaces\iClient;
use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\ApiClient\Interfaces\Request\iApiMethod;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Http\Interfaces\iHeaderCollection;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Psr\Interfaces\RequestInterface;
use Poirot\HttpAgent\Connection\HAStreamConn;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Interfaces\iSeqPathUri;
use Poirot\PathUri\Psr\UriInterface;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Psr\StreamInterface;

class Agent implements iClient
{
    /** @var HAStreamConn|iConnection*/
    protected $connection;
    /** @var HttpPlatform */
    protected $platform;
    /** @var AgentOptions */
    protected $options;

    /**
     * Construct
     *
     * @param AgentOptions|iDataSetConveyor|null $options
     */
    function __construct($options = null)
    {

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
            $this->platform = new HttpPlatform;

        return $this->platform;
    }

    /**
     * Get Connection Adapter
     *
     * @return HAStreamConn
     */
    function connection()
    {
        if (!$this->connection)
            $this->connection = new HAStreamConn;

        return $this->connection;
    }


    // ...

    /**
     * Send HTTP OPTIONS request to server
     *
     * - using absolute url as target not depend on current request base url
     *
     * - create method build from platform, platform will build request object from that
     *
     * @param string|iHttpUri|UriInterface $targetUri Relative Uri that merged into base url
     *
     * @return iHttpRequest
     */
    function OPTIONS($targetUri) {}

    /**
     * @param string|iSeqPathUri|iHttpUri|UriInterface $targetUri
     * @param array|iHeaderCollection|null             $headers
     * @param array|iDataSetConveyor|null              $options
     */
    function GET($targetUri, $headers = null, $options = null) {}

    function HEAD($targetUri, $headers = null, $options = null) {}

    /**
     * @param string|iSeqPathUri|iHttpUri|UriInterface         $targetUri
     * @param string|iStreamable|StreamInterface|resource|null $body
     * @param array|iHeaderCollection|null                     $headers
     * @param array|AgentOptions|iDataSetConveyor|null         $options
     *                                                         Agent Options To Merge With Default Agent Options
     */
    function PATCH($targetUri, $body = null, $headers = null, $options = null) {}

    function POST($targetUri, $body = null, $headers = null, $options = null) {}

    function PUT($targetUri, $body = null, $headers = null, $options = null) {}

    function DELETE($targetUri, $body = null, $headers = null, $options = null) {}

    function TRACE($targetUri) {}

    function CONNECT($targetUri) {}

    /**
     * Send Request Object Via Connection
     *
     * ! if request object is null must using latest request method built
     *
     * @param iHttpRequest|RequestInterface|null $request
     *
     * @return iResponse
     */
    function send($request = null) {}

    /**
     * !! WE HAVE CUSTOM METHOD REQUESTS
     *
     * @inheritdoc
     * @throws \RuntimeException
     */
    function call(iApiMethod $method)
    {
        throw new \RuntimeException(
            'Direct call not available for generic Http Agent.'
            .' using sendGET, sendPUT, sendPOST, ... Methods instead.'
        );
    }

    protected function _f__call(iApiMethod $method)
    {
        $platform = $this->platform();

        $expr     = $platform->makeExpression($method);
        $result   = $this->connection()->exec($expr);

        $response = $platform->makeResponse($result);
        return $response;
    }

    // ...

}
