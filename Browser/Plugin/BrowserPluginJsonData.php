<?php
namespace Poirot\HttpAgent\Browser\Plugin;

use Poirot\Http\Header\FactoryHttpHeader;
use Poirot\Http\Interfaces\iHttpRequest;

use Poirot\HttpAgent\Interfaces\iBrowserExpressionPlugin;


/*
$data = $browser->POST('/api/v1/auth/login'
    , [ 'json' // <=== send application/json to server
    => [
            'email'    => 'naderi.payam@gmail.com',
            'password' => '123456'
        ]
    ]
)->getResult(new Browser\Plugin\BJsonPlugin());

// ===================================================

$data = $browser->POST('/api/v1/auth/login'
    , [ 'form_data'
        => [
            'email'    => 'naderi.payam@gmail.com',
            'password' => '123456'
        ]
    ]
)->getResult()->plg()->json();


echo $data->token;

*/

class BrowserPluginJsonData extends BaseBrowserPlugin
    implements iBrowserExpressionPlugin
{
    protected $_t_options__internal = array(
        'setMessageObject', // this method will ignore as option in prop
        'getMessageObject',
    );

    
    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     */
    function withHttpRequest(iHttpRequest $request)
    {
        $params = \Poirot\Std\cast($this)->toArray();

        $body   = json_encode($params);
        $request->setBody($body);

        $request->headers()->insert(
            FactoryHttpHeader::of( array('Content-Type' => 'application/json') )
        );
    }
}
