<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\Std\Interfaces\Struct\iDataOptions;

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

interface iPluginBrowser
    extends iDataOptions
{ }
