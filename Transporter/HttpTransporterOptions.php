<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Core\AbstractOptions;
use Poirot\PathUri\HttpUri;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Psr\UriInterface;

class HttpTransporterOptions extends AbstractOptions
{
    protected $serverUrl;
    protected $timeout;
    protected $persistent;

    /** @var bool Http Transporter Allowed To Decode Body Response */
    protected $allowedDecoding = true;

    /**
     * Server Url That we Will Connect To
     * @param iHttpUri|UriInterface|string $serverUrl
     * @return $this
     */
    public function setServerUrl($serverUrl)
    {
        if (is_string($serverUrl))
            $serverUrl = new HttpUri($serverUrl);
        elseif ($serverUrl instanceof UriInterface)
            $serverUrl = new HttpUri($serverUrl);

        if (!$serverUrl instanceof iHttpUri)
            throw new \InvalidArgumentException(sprintf(
                'Server Url must instance of iHttpUri, UriInterface or string representing url address. given: "%s".'
                , \Poirot\Core\flatten($serverUrl)
            ));

        $this->serverUrl = $serverUrl;

        return $this;
    }

    /**
     * @return iHttpUri
     */
    public function getServerUrl()
    {
        return $this->serverUrl;
    }

    /**
     * @param mixed $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param mixed $persistent
     * @return $this
     */
    public function setPersistent($persistent)
    {
        $this->persistent = (bool) $persistent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPersistent()
    {
        return $this->persistent;
    }


    // ...

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
    public function setAllowedDecoding($allowedDecoding)
    {
        $this->allowedDecoding = $allowedDecoding;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAllowedDecoding()
    {
        return $this->allowedDecoding;
    }
}
