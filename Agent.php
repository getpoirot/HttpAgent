<?php
namespace Poirot\HttpAgent;

use Poirot\ApiClient\AbstractClient;
use Poirot\ApiClient\Interfaces\iConnection;
use Poirot\ApiClient\Interfaces\iPlatform;
use Poirot\HttpAgent\Connection\HAStreamConn;

class Agent extends AbstractClient
{
    /** @var iConnection*/
    protected $connection;
    /** @var iPlatform */
    protected $platform;

    /**
     * Get Client Platform
     *
     * - used by request to build params for
     *   server execution call and response
     *
     * @return iPlatform
     */
    function platform()
    {
        if (!$this->platform)
            $this->platform = new HttpPlatform;

        return $this->platform;
    }

    /**
     * Get Connection Adapter
     *
     * @return iConnection
     */
    function connection()
    {
        if (!$this->connection)
            $this->connection = new HAStreamConn;

        return $this->connection;
    }
}
