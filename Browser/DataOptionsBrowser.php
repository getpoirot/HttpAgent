<?php
namespace Poirot\HttpAgent\Browser;

use Poirot\Std\Struct\DataOptionsOpen;

use Poirot\HttpAgent\Transporter\HttpTransporterOptions;


/**
 * This is open options because may contains options for attached plugins-
 * and used on platform
 * 
 */
class DataOptionsBrowser 
    extends DataOptionsOpen
{
    /** @var string Base Url to Server */
    protected $baseUrl;
    protected $userAgent;

    # default element options
    /** @var HttpTransporterOptions */
    protected $connectionOptions;
    /** @var DataOptionsBrowserRequest */
    protected $requestOptions;
    /** @var array */
    protected $pluginsOptions;


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

    /**
     * Set Plugins Services Settings
     * @see BuildContainer
     * 
     * @param array $pluginsOptions
     * 
     * @return $this
     */
    function setPluginsOptions(array $pluginsOptions)
    {
        $this->pluginsOptions = $pluginsOptions;
        return $this;
    }

    /**
     * Get Plugins Setting 
     * @return array
     */
    function getPluginsOptions()
    {
        return $this->pluginsOptions;
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
     * Set Http Request Object Options Params
     * @param mixed $requestOptions
     * @return $this
     */
    function setRequestOptions($requestOptions)
    {
        $this->getRequestOptions()->import($requestOptions);
        return $this;
    }

    /**
     * Get Http Request Object Options Params
     * @return DataOptionsBrowserRequest
     */
    function getRequestOptions()
    {
        if (!$this->requestOptions)
            $this->requestOptions = new DataOptionsBrowserRequest;

        return $this->requestOptions;
    }
}
