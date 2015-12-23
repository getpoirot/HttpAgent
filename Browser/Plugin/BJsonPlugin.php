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

class BJsonPlugin extends AbstractBrowserPlugin
    implements iBrowserExpressionPlugin
    , iBrowserResponsePlugin
    , iHttpPlugin
{
    use ResponsePluginTrait; // Implement Http Response Plugable

    /**
     * note: can executed with invokablePlugin directly to get result
     *       $ResponsePlatform->getResult()->plg()->json();
     */
    function __invoke()
    {
        $response = $this->getMessageObject();
        $body     = $response->getBody()->rewind()->read();

        return json_decode($body);
    }

    /**
     * Manipulate Http Request
     *
     * @param iHttpRequest $request
     */
    function withHttpRequest(iHttpRequest $request)
    {
        $params = $this->toArray();

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
