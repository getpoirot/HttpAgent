<?php
namespace Poirot\HttpAgent\Platform;

use Poirot\ApiClient\ResponseOfClient;

use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHeader;

use Poirot\Stream\Interfaces\iStreamable;
use Psr\Http\Message\ResponseInterface;


class ResponsePlatform 
    extends ResponseOfClient
{
    /**
     * Construct
     *
     * @param ResponseInterface $response
     */
    function __construct(ResponseInterface $response)
    {
        $this->rawbody = $response;

        $this->setRawBody($response->getBody());

        /** @var iHeader $h */
        foreach($response->getHeaders() as $h => $_)
            $this->meta()->__set($h, $response->getHeaderLine($h));

        $code = $response->getStatusCode();
        if (!(200 <= $code && $code < 300))
            $this->setException(new \RuntimeException($response->getReasonPhrase(), $response->getStatusCode()));
        
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
        $this->rawbody = $content;
        return $this;
    }

    /**
     * Get Response Origin Body Content
     *
     * @return iStreamable|string
     */
    function getRawBody()
    {
        return $this->rawbody;
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
