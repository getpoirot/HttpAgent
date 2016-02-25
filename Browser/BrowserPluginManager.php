<?php
namespace Poirot\HttpAgent\Browser;

use Poirot\Container\Exception\ContainerInvalidPluginException;
use Poirot\Container\Interfaces\iContainerBuilder;
use Poirot\Container\Plugins\AbstractPlugins;
use Poirot\HttpAgent\Interfaces\iBrowserPlugin;

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
class BrowserPluginManager extends AbstractPlugins
{
    protected $loader_resources = [
        'form_data' => 'Poirot\HttpAgent\Browser\Plugin\BFormDataPlugin',
        'json'      => 'Poirot\HttpAgent\Browser\Plugin\BJsonPlugin',
    ];

    /**
     * @override
     *
     * Construct
     *
     * @param iContainerBuilder $cBuilder
     *
     * @throws \Exception
     */
    function __construct(iContainerBuilder $cBuilder = null)
    {
        parent::__construct($cBuilder);

        // Add Initializer To Inject Http Message Instance:

    }

    /**
     * Validate Plugin Instance Object
     *
     * @param mixed $pluginInstance
     *
     * @throws ContainerInvalidPluginException
     * @return void
     */
    function validatePlugin($pluginInstance)
    {
        if (!$pluginInstance instanceof iBrowserPlugin)
            throw new ContainerInvalidPluginException(sprintf(
                'Invalid Plugin Provided For (%s).'
                , \Poirot\Std\flatten($pluginInstance)
            ));
    }
}
