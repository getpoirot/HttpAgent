<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Core\AbstractOptions;
use Poirot\Core\OpenOptions;

class StreamHttpEventCollector extends OpenOptions
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
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getTransporter()
    {
        return $this->transporter;
    }

    /**
     * @param mixed $transporter
     */
    public function setTransporter($transporter)
    {
        $this->transporter = $transporter;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    // ...

    /**
     * @return mixed
     */
    public function getContinue()
    {
        return $this->continue;
    }

    /**
     * @param mixed $continue
     */
    public function setContinue($continue)
    {
        $this->continue = $continue;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
}
