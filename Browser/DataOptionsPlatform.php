<?php
namespace Poirot\HttpAgent\Browser;

use Poirot\Std\Struct\DataOptionsOpen;

use Poirot\HttpAgent\Transporter\HttpTransporterOptions;


/**
 * This is open options because may contains options for attached plugins-
 * and used on platform
 * 
 */
class DataOptionsPlatform 
    extends DataOptionsOpen
{
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
