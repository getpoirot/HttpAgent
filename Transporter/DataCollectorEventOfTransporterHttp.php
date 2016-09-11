<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Std\Struct\DataOptionsOpen;

class TransporterHttpEventCollector 
    extends DataOptionsOpen
{
    protected $response;
    protected $transporter;
    protected $request;

    // get back results
    protected $continue = true;
    protected $body;

    /**
     * @return mixed
     */
    function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    function setResponse($response)
    {
        $this->response = $response;
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
    function getContinue()
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
}
