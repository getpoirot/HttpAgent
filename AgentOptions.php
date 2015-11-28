<?php
namespace Poirot\HttpAgent;

use Poirot\Core\AbstractOptions;
use Poirot\HttpAgent\Interfaces\iHAgentOptions;
use Poirot\PathUri\HttpUri;
use Poirot\PathUri\Interfaces\iHttpUri;

class AgentOptions extends AbstractOptions
    implements iHAgentOptions
{
    /** @var iHttpUri */
    protected $baseUrl;

    /**
     * Set Base Url
     *
     * @param iHttpUri|string $baseUrl
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setBaseUrl($baseUrl)
    {
        if (is_string($baseUrl))
            $baseUrl = new HttpUri($baseUrl);

        if (!$baseUrl instanceof HttpUri)
            throw new \InvalidArgumentException('Base Url must instance of iHttpUri or string represent url.');

        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Get Base Url
     *
     * @throws \InvalidArgumentException
     * @return iHttpUri
     */
    function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set Maximum Redirect Follow Count
     *
     * @param int $count
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setRedirectFollow($count)
    {
        // TODO: Implement setRedirectFollow() method.
    }

    /**
     * Get Max Redirect Follow
     *
     * @throws \InvalidArgumentException
     * @return int
     */
    function getRedirectFollow()
    {
        // TODO: Implement getRedirectFollow() method.
    }
}
