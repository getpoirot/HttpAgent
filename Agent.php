<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\Interfaces\iClient;
use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\ApiClient\Interfaces\Request\iApiMethod;
use Poirot\ApiClient\Interfaces\Response\iResponse;
use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Core\Interfaces\OptionsProviderInterface;
use Poirot\Http\Interfaces\iHeaderCollection;
use Poirot\HttpAgent\Connection\HAStreamConn;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Interfaces\iSeqPathUri;
use Poirot\PathUri\Psr\UriInterface;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Psr\StreamInterface;

class Agent implements iClient
    , OptionsProviderInterface
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
        if ($options !== null)
            $this->options()->from($options);
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
     * @param string|iHttpUri|UriInterface $targetUri Relative Uri that merged into base url
     *
     * @return iResponse
     */
    function sendOPTIONS($targetUri) {}

    /**
     * @param string|iSeqPathUri|iHttpUri|UriInterface $targetUri
     * @param array|iHeaderCollection|null             $headers
     * @param array|iDataSetConveyor|null              $options
     */
    function sendGET($targetUri, $headers = null, $options = null) {}

    function sendHEAD($targetUri, $headers = null, $options = null) {}

    /**
     * @param string|iSeqPathUri|iHttpUri|UriInterface         $targetUri
     * @param string|iStreamable|StreamInterface|resource|null $body
     * @param array|iHeaderCollection|null                     $headers
     * @param array|iDataSetConveyor|null                      $options
     */
    function sendPATCH($targetUri, $body = null, $headers = null, $options = null) {}

    function sendPOST($targetUri, $body = null, $headers = null, $options = null) {}

    function sendPUT($targetUri, $body = null, $headers = null, $options = null) {}

    function sendDELETE($targetUri, $body = null, $headers = null, $options = null) {}

    function sendTRACE($targetUri) {}

    function sendCONNECT($targetUri) {}

    /**
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

    protected function _v__call(iApiMethod $method)
    {
        $platform = $this->platform();

        $expr     = $platform->makeExpression($method);
        $result   = $this->connection()->exec($expr);

        $response = $platform->makeResponse($result);
        return $response;
    }


    // ...

    /**
     * @inheritdoc
     *
     * @return AgentOptions
     */
    function options()
    {
        if (!$this->options)
            $this->options = static::optionsIns();

        return $this->options;
    }

    /**
     * @inheritdoc
     *
     * @return AgentOptions
     */
    static function optionsIns()
    {
        return new AgentOptions;
    }
}
