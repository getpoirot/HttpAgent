<?php
namespace Poirot\HttpAgent\Platform;

use Poirot\ApiClient\ResponseOfClient;

use Poirot\Http\HttpMessage\Response\Plugin\Status;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHeader;

use Poirot\Http\Interfaces\iHttpResponse;
use Poirot\Stream\Interfaces\iStreamable;


class ResponsePlatform 
    extends ResponseOfClient
{
    /**
     * Construct
     *
     * @param iHttpResponse $response
     */
    function __construct(iHttpResponse $response)
    {
        $this->rawbody = $response;

        $this->setRawBody($response->getBody());

        /** @var iHeader $h */
        foreach($response->headers() as $h)
            $this->meta()->__set($h->getLabel(), $h);

        $statusPlugin = Status::_($response);
        if (!$statusPlugin->isSuccess())
            $this->setException(new \RuntimeException($response->getStatusReason(), $response->getStatusCode()));
        
        parent::__construct();
    }


    // ...

    /**
     * Set Response Origin Content
     *
     * @param iStreamable|string $content Content Body
     *
     * @return $this
     */
    function setRawBody($content)
    {
        $this->rawbody->setBody($content);
        return $this;
    }

    /**
     * Get Response Origin Body Content
     *
     * @return iStreamable|string
     */
    function getRawBody()
    {
        return $this->rawbody->getBody();
    }


    // ...

    /**
     * @override ide completion
     * @param callable|null $callable
     * @return HttpResponse
     */
    function expected(callable $callable = null)
    {
        return parent::expected($callable);
    }
}
