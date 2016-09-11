<?php
namespace Poirot\HttpAgent;

use Poirot\Std\Struct\DataOptionsOpen;

use Poirot\HttpAgent\Transporter\HttpTransporterOptions;


/**
 * This is open options because may contains options for attached plugins
 */
class BrowserOptions 
    extends DataOptionsOpen
{
    /** @var string Base Url to Server */
    protected $baseUrl;
    protected $userAgent;

    # default element options
    /** @var HttpTransporterOptions */
    protected $connectionOptions;
    /** @var BrowserRequestOptions */
    protected $requestOptions;


    /**
     * @param string $baseUrl
     * @return $this
     */
    function setBaseUrl($baseUrl)
    {
        $this->baseUrl = (string) $baseUrl;
        return $this;
    }

    /**
     * @return string|null
     */
    function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $userAgent
     * @return $this
     */
    function setUserAgent($userAgent)
    {
        $this->userAgent = (string) $userAgent;
        return $this;
    }

    /**
     * @return mixed
     */
    function getUserAgent()
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
     * @param array|\Traversable|HttpTransporterOptions $connectionOptions
     * @return $this
     */
    function setConnectionOptions($connectionOptions)
    {
        $this->getConnectionOptions()->import($connectionOptions);
        return $this;
    }

    /**
     * @return HttpTransporterOptions
     */
    function getConnectionOptions()
    {
        if (!$this->connectionOptions)
            $this->connectionOptions = new HttpTransporterOptions;

        return $this->connectionOptions;
    }

    /**
     * Set Request Options Params
     * @param mixed $requestOptions
     * @return $this
     */
    function setRequestOptions($requestOptions)
    {
        $this->getRequestOptions()->import($requestOptions);
        return $this;
    }

    /**
     * @return BrowserRequestOptions
     */
    function getRequestOptions()
    {
        if (!$this->requestOptions)
            $this->requestOptions = new BrowserRequestOptions;

        return $this->requestOptions;
    }
}
