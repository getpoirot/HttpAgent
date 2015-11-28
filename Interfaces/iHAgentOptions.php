<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\Core\Interfaces\iPoirotOptions;
use Poirot\PathUri\Interfaces\iHttpUri;

interface iHAgentOptions extends iPoirotOptions
{
    /**
     * Set Base Url
     *
     * @param iHttpUri|string $baseUrl
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setBaseUrl($baseUrl);

    /**
     * Get Base Url
     *
     * - remember host of last request
     * // if host changed, the HTTP authentication should be cleared for security
     * // reasons, see #4215 for a discussion - currently authentication is also
     * // cleared for peer subdomains due to technical limits
     * // Set auth if username and password has been specified in the uri
     *
     * @throws \InvalidArgumentException
     * @return iHttpUri
     */
    function getBaseUrl();

    /**
     * Set Maximum Redirect Follow Count
     *
     * @param int $count
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setRedirectFollow($count);

    /**
     * Get Max Redirect Follow
     *
     * @throws \InvalidArgumentException
     * @return int
     */
    function getRedirectFollow();
}
