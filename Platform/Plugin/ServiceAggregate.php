<?php
namespace Poirot\HttpAgent\Platform\Plugin;

use Poirot\HttpAgent\Interfaces\Browser\iPluginBrowser;
use Poirot\Ioc\Container\Service\aServiceAggregate;

class ServiceAggregate
    extends aServiceAggregate
{
    protected $_services_map = array(
        PluginFormData::SERVICE_NAME          => __NAMESPACE__.'\\PluginFormData',
        PluginFormUrlEncodeData::SERVICE_NAME => __NAMESPACE__.'\\PluginFormUrlEncodeData',
        PluginJsonData::SERVICE_NAME          => __NAMESPACE__.'\\PluginJsonData',
    );

    
    /**
     * Determine Which Can Create Service With Given Name?
     *
     * @param string $serviceName
     *
     * @return boolean
     */
    function canCreate($serviceName)
    {
        return isset($this->_services_map[$serviceName]);
    }

    /**
     * Create Service
     *
     * @return iPluginBrowser
     * @throws \Exception
     */
    function newService()
    {
        if (null === $serviceRequested = $this->currentService)
            throw new \Exception('Aggregate Service Not Initialized WithServiceName Yet!');

        $class = $this->_services_map[$serviceRequested];

        $serviceAttained = new $class($this->optsData());
        return $serviceAttained;
    }
}
