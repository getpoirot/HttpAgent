<?php
namespace Poirot\HttpAgent\Platform;

use Poirot\ApiClient\ResponseOfClient;

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
        $this->response = $response;

        # meta
        foreach($response->getHeaders() as $h => $_)
            $this->meta()->__set($h, $response->getHeaderLine($h));

        # body
        $this->setRawResponse($response);

        # exception
        $code = $response->getStatusCode();
        if (!(200 <= $code && $code < 300))
            $this->setException(new \RuntimeException($response->getReasonPhrase(), $response->getStatusCode()));
        
        parent::__construct();
    }


    // ...

    /**
     * Set Response Origin Content
     *
     * @param ResponseInterface $response
     *
     * @return $this
     */
    function setRawResponse($response)
    {
        $this->rawResponse = $response;
        return $this;
    }

    /**
     * Get Response Origin Body Content
     *
     * @return ResponseInterface
     */
    function getRawResponse()
    {
        return $this->rawResponse;
    }


    // ...

    /**
     * @override ide completion
     * @param callable|null $callable
     *
     * @return ResponseInterface
     */
    function expected(callable $callable = null)
    {
        return parent::expected($callable);
    }
}
