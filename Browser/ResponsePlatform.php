<?php
namespace Poirot\HttpAgent\Browser;

use Poirot\ApiClient\ResponseOfClient;

use Poirot\Http\HttpMessage\Response\Plugin\Status;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHeader;

use Poirot\Stream\Interfaces\iStreamable;


class ResponsePlatform 
    extends ResponseOfClient
{
    /**
     * Construct
     *
     * @param HttpResponse $response
     */
    function __construct(HttpResponse $response)
    {
        $this->rawbody = $response;

        $this->setRawBody($response->getBody());

        /** @var iHeader $h */
        foreach($response->getHeaders() as $h)
            $this->meta()->set($h->getLabel(), $h);

        $statusPlugin = Status::_($response);
        if (!$statusPlugin->isSuccess())
            $this->setException(new \RuntimeException($response->getStatusReason(), $response->getStatusCode()));
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
     * @param callable|null $proc
     * @return HttpResponse
     */
    function expected(callable $proc = null)
    {
        return parent::expected($proc);
    }
}
