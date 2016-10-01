<?php
namespace Poirot\HttpAgent\Platform;

use Poirot\HttpAgent\Interfaces\Browser\iPluginBrowser;
use Poirot\HttpAgent\Platform\Plugin\ServiceAggregate;
use Poirot\Ioc\Container\aContainerCapped;
use Poirot\Ioc\Container\BuildContainer;
use Poirot\Ioc\Container\Exception\exContainerInvalidServiceType;



/**
 * Browser Plugins Will Triggered When Options Name Same As Plugin
 * Registered Name Passed With Request Method.
 * [code:]
 *   $browser->POST('/api/v1/auth/login', [
 *      'form_data' => [ // <=== plugin form_data will trigger with this params
 *         'username' => 'naderi.payam@gmail.com',
 *         'password' => '123456',
 *      ]
 *   ])
 * [code]
 */
class PluginsOfBrowser
    extends aContainerCapped
{
    /**
     * @override
     *
     * Construct
     *
     * @param BuildContainer $cBuilder
     *
     * @throws \Exception
     */
    function __construct(BuildContainer $cBuilder = null)
    {
        parent::__construct($cBuilder);
        
        $this->set(new ServiceAggregate);
    }

    /**
     * Validate Plugin Instance Object
     *
     * @param mixed $pluginInstance
     *
     * @throws exContainerInvalidServiceType
     * @return void
     */
    function validateService($pluginInstance)
    {
        if (!$pluginInstance instanceof iPluginBrowser)
            throw new exContainerInvalidServiceType(sprintf(
                'Invalid Plugin Provided For (%s).'
                , \Poirot\Std\flatten($pluginInstance)
            ));
    }
}
