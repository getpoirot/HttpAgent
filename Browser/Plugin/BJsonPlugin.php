<?php
namespace Poirot\HttpAgent\Browser\Plugin;

use Poirot\Container\Service\InstanceService;
use Poirot\Http\Header\HeaderFactory;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Interfaces\Message\iHttpResponse;
use Poirot\Http\Message\HttpResponse;
use Poirot\Http\Plugins\iHttpPlugin;
use Poirot\Http\Plugins\Response\ResponsePluginTrait;
use Poirot\HttpAgent\Interfaces\iBrowserExpressionPlugin;
use Poirot\HttpAgent\Interfaces\iBrowserResponsePlugin;

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

class BJsonPlugin extends AbstractBrowserPlugin
    implements iHttpPlugin
    , iBrowserExpressionPlugin
    , iBrowserResponsePlugin
{
    use ResponsePluginTrait; // Implement Http Response Plugable
    protected $_t_options__internal = [
        'setMessageObject', // this method will ignore as option in prop
        'getMessageObject',
    ];

    /**
     * note: can executed with invokablePlugin directly to get result
     *       $ResponsePlatform->getResult()->plg()->json();
     *
     *       also can be used as:
     *       $ResponsePlatform->getResult(new BjsonPlugin);
     *
     * @param null|iHttpResponse $response Can used as functor on :getResult()
     *
     * @return \stdClass
     */
    function __invoke(iHttpResponse $response = null)
    {
        ($response !== null) ?: $response = $this->getMessageObject();

        $body = $response->getBody()->rewind()->read();
        return json_decode($body);
    }

    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     */
    function withHttpRequest(iHttpRequest $request)
    {
        $params = \Poirot\Std\iterator_to_array($this);

        $body   = json_encode($params);
        $request->setBody($body);

        $request->getHeaders()->set(HeaderFactory::factory('Content-Type', 'application/json'));
    }

    /**
     * Manipulate Http Response
     *
     * @param HttpResponse|iHttpResponse $response
     */
    function withHttpResponse(iHttpResponse $response)
    {
        if(!$response->getPluginManager()->has('json'))
            $response->getPluginManager()->set(new InstanceService('json', $this));
    }
}
