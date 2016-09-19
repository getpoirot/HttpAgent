<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Std\Struct\DataOptionsOpen;
use Poirot\Stream\Interfaces\iStreamable;

class DataCollectorEventOfTransporterHttp
    extends DataOptionsOpen
{
    protected $response;
    protected $parsedResponse;
    protected $transporter;
    protected $request;

    // get back results
    protected $continue = true;
    protected $body;

    /**
     * @return mixed
     */
    function getParsedResponse()
    {
        return $this->parsedResponse;
    }

    /**
     * @param mixed $response
     */
    function setParsedResponse($response)
    {
        $this->parsedResponse = $response;
    }

    /**
     * @return mixed
     */
    function getTransporter()
    {
        return $this->transporter;
    }

    /**
     * @param mixed $transporter
     */
    function setTransporter($transporter)
    {
        $this->transporter = $transporter;
    }

    /**
     * @return mixed
     */
    function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    function setRequest($request)
    {
        $this->request = $request;
    }

    // ...

    /**
     * @return mixed
     */
    function isContinue()
    {
        return $this->continue;
    }

    /**
     * @param mixed $continue
     */
    function setContinue($continue)
    {
        $this->continue = $continue;
    }

    /**
     * @return mixed
     */
    function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return iStreamable
     */
    function getResponse()
    {
        return $this->response;
    }

    /**
     * @param iStreamable $response
     */
    function setResponse($response)
    {
        $this->response = $response;
    }
}
