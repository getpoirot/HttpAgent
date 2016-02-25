<?php
namespace Poirot\HttpAgent;

use Poirot\Std\Interfaces\Struct\iDataStruct;
use Poirot\Std\Struct\OpenOptionsData;
use Poirot\Std\Traits\CloneTrait;
use Poirot\HttpAgent\Transporter\HttpTransporterOptions;
use Poirot\PathUri\HttpUri;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Psr\UriInterface;

/**
 * This is open options because may contains options for attached plugins
 */
class BrowserOptions extends OpenOptionsData
{
    use CloneTrait;

    /** @var string|iHttpUri|UriInterface Base Url to Server */
    protected $baseUrl    = VOID;
    protected $userAgent  = VOID;

    # default element options
    /** @var HttpTransporterOptions */
    protected $connection;
    /** @var BrowserRequestOptions */
    protected $request;


    /**
     * @param iHttpUri|UriInterface|string $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        if (is_string($baseUrl) || $baseUrl instanceof UriInterface)
            $baseUrl = new HttpUri($baseUrl);

        if (!$baseUrl instanceof iHttpUri)
            throw new \InvalidArgumentException(sprintf(
                'BaseUrl must instance of iHttpUri, UriInterface or string. given: "%s"'
                , \Poirot\Std\flatten($baseUrl)
            ));

        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return iHttpUri|VOID
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
        $this->userAgent = (string) $userAgent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        if (!$this->userAgent || $this->userAgent === VOID) {
            $userAgent = '';

            if (!$userAgent) {
                $userAgent = 'PoirotBrowser';
                $userAgent .= '-PHP/' . PHP_VERSION;
            }

            $this->setUserAgent($userAgent);
        }

        return $this->userAgent;
    }


    // ...

    /**
     * Set Connection Options
     *
     * @param array|iDataStruct|HttpTransporterOptions $connection
     * @return $this
     */
    public function setConnection($connection)
    {
        // TODO catch exception for bright exception message
        $this->getConnection()->from($connection);
        return $this;
    }

    /**
     * @return HttpTransporterOptions
     */
    public function getConnection()
    {
        if (!$this->connection || $this->connection === VOID)
            $this->connection = new HttpTransporterOptions;

        return $this->connection;
    }

    /**
     * Set Request Options Params
     * @param mixed $request
     * @return $this
     */
    public function setRequest($request)
    {
        // TODO catch exception for bright exception message
        $this->getRequest()->from($request);
        return $this;
    }

    /**
     * @return BrowserRequestOptions
     */
    public function getRequest()
    {
        if (!$this->request || $this->request === VOID)
            $this->request = new BrowserRequestOptions;

        return $this->request;
    }
}
