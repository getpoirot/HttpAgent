<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\ApiClient\Transporter\HttpSocketOptions;
use Poirot\Core\AbstractOptions;
use Poirot\Core\Traits\CloneTrait;
use Poirot\PathUri\HttpUri;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Psr\UriInterface;

class HttpTransporterOptions extends HttpSocketOptions
{
    use CloneTrait;

    /** @var bool Http Transporter Allowed To Decode Body Response */
    protected $allowedDecoding = true;

    /**
     * @override can give uri objects
     * Server Url That we Will Connect To
     *
     * @param iHttpUri|UriInterface|string $serverUrl
     *
     * @return $this
     */
    public function setServerUrl($serverUrl)
    {
        if ($serverUrl instanceof UriInterface)
            $serverUrl = new HttpUri($serverUrl);

        if ($serverUrl instanceof iHttpUri)
            $serverUrl->toString();

        if (is_object($serverUrl))
            $serverUrl = (string) $serverUrl;

        if (!is_string($serverUrl))
            throw new \InvalidArgumentException(sprintf(
                'Server Url must instance of iHttpUri, UriInterface or string representing url address. given: "%s".'
                , \Poirot\Core\flatten($serverUrl)
            ));

        $this->serverUrl = $serverUrl;
        return $this;
    }


    // ... TODO move outside

    /**
     * note: some times we need raw body from response
     *       without any modification or filters added
     *       it maybe used when we use StreamHttp as a
     *       proxy or want to flush response directly
     *       into output that handle decoding itself.
     *
     * @param boolean $allowedDecoding
     * @return $this
     */
    public function setAllowDecoding($allowedDecoding)
    {
        $this->allowedDecoding = $allowedDecoding;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAllowDecoding()
    {
        return $this->allowedDecoding;
    }
}
