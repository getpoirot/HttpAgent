<?php
namespace Poirot\HttpAgent\Transporter\Listeners;

use Poirot\Core\AbstractOptions;

class StreamHttpEventCollector extends AbstractOptions
{
    protected $response;
    protected $transporter;
    protected $request;

    protected $continue = true;

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
}
