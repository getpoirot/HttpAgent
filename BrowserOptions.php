<?php
namespace Poirot\HttpAgent;

use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\iDataSetConveyor;
use Poirot\Core\OpenOptions;
use Poirot\Core\Traits\CloneTrait;
use Poirot\HttpAgent\Transporter\HttpTransporterOptions;
use Poirot\PathUri\HttpUri;
use Poirot\PathUri\Interfaces\iHttpUri;
use Poirot\PathUri\Psr\UriInterface;

/**
 * This is open options because may contains options for attached plugins
 */
class BrowserOptions extends OpenOptions
{
    use CloneTrait;

    /** @var string|iHttpUri|UriInterface Base Url to Server */
    protected $baseUrl;
    protected $userAgent;

    # default element options
    protected $connection;
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
                , \Poirot\Core\flatten($baseUrl)
            ));

        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return iHttpUri
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
        if (!$this->userAgent) {
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
     * @param array|iDataSetConveyor|HttpTransporterOptions $connection
     * @return $this
     */
    public function setConnection($connection)
    {
        /** ALWAYS KEEP LAST VALUES AND NOT REPLACE WHOLE */

        $tConnection = ($this->connection) ? $this->connection : new HttpTransporterOptions;

        if (!$connection instanceof HttpTransporterOptions && $connection !== null)
            $connection = new HttpTransporterOptions($connection);

        foreach($connection->props()->readable as $prop)
            if (($val = $connection->__get($prop)) !== null)
                $tConnection->__set($prop, $val);

        $this->connection = $tConnection;
        return $this;
    }

    /**
     * @return null|HttpTransporterOptions
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set Request Options Params
     * @param mixed $request
     * @return $this
     */
    public function setRequest($request)
    {
        /** ALWAYS KEEP LAST VALUES AND NOT REPLACE WHOLE */

        $tRequest = ($this->request) ? $this->request : new BrowserRequestOptions;

        if (!$request instanceof BrowserRequestOptions && $request !== null)
            $request = new BrowserRequestOptions($request);

        foreach($request->props()->readable as $prop) {
            if (($val = $request->__get($prop)) !== null) {
                $tRequest->__set($prop, $val);
            }
        }

        $this->request = $tRequest;
        return $this;
    }

    /**
     * @return BrowserRequestOptions
     */
    public function getRequest()
    {
        return $this->request;
    }
}
