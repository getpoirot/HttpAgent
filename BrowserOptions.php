<?php
namespace Poirot\HttpAgent;

use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Core\OpenOptions;
use Poirot\HttpAgent\Transporter\StreamHttpTransporterOptions;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Psr\UriInterface;

/**
 * This is open options because may contains options for attached plugins
 */
class BrowserOptions extends OpenOptions
{
    /** @var string|iHttpUri|UriInterface Base Url to Server */
    protected $baseUrl;

    # default element options
    protected $connection;
    protected $request;

    protected $userAgent;


    /**
     * @param iHttpUri|UriInterface|string $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return iHttpUri|UriInterface|string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        if (!$this->userAgent) {
            $userAgent = '';

            if (!$userAgent) {
                $userAgent = 'PoirotBrowser ';
                $userAgent .= ' PHP/' . PHP_VERSION;
            }

            $this->setUserAgent($userAgent);
        }

        return $this->userAgent;
    }


    // ...

    /**
     * Set Connection Options
     *
     * @param array|iDataSetConveyor|StreamHttpTransporterOptions $connection
     * @return $this
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return array|iDataSetConveyor|StreamHttpTransporterOptions
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
