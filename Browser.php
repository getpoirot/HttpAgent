<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\AbstractClient;
use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Http\Interfaces\iHeaderCollection;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\HttpAgent\Transporter\StreamHttpTransporter;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Interfaces\iSeqPathUri;
use Poirot\PathUri\Psr\UriInterface;
use Poirot\Stream\Interfaces\iStreamable;
use Poirot\Stream\Psr\StreamInterface;

class Browser extends AbstractClient
{
    /** @var StreamHttpTransporter|iConnection*/
    protected $connection;
    /** @var HttpPlatform */
    protected $platform;
    /** @var BrowserOptions */
    protected $options;

    /**
     * Construct
     *
     * @param BrowserOptions|iDataSetConveyor|null $options
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
     * @return StreamHttpTransporter
     */
    function connection()
    {
        if (!$this->connection)
            $this->connection = new StreamHttpTransporter;

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
     * @param array|BrowserOptions|iDataSetConveyor|null       $options
     *                                                         Agent Options To Merge With Default Agent Options
     */
    function PATCH($targetUri, $body = null, $headers = null, $options = null) {}

    function POST($targetUri, $body = null, $headers = null, $options = null) {}

    function PUT($targetUri, $body = null, $headers = null, $options = null) {}

    function DELETE($targetUri, $body = null, $headers = null, $options = null) {}

    function TRACE($targetUri) {}

    function CONNECT($targetUri) {}
}
