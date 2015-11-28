<?php
namespace Poirot\HttpAgent\Interfaces;

use Poirot\ApiClient\Interfaces\iClient;
use Poirot\Core\AbstractOptions;
use Poirot\Core\Interfaces\OptionsProviderInterface;
use Poirot\Http\Interfaces\Message\iHttpRequest;
use Poirot\Http\Interfaces\Message\iHttpResponse;
use Poirot\Http\Psr\Interfaces\RequestInterface;

interface iHttpAgent extends iClient, OptionsProviderInterface
{
    /**
     * Set Request
     *
     * @param iHttpRequest|RequestInterface $request
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    function setRequest($request);

    /**
     * Request Http
     *
     * @return iHttpRequest
     */
    function request();

    /**
     * Send Http Request
     *
     * - use given request argument if exists instead of
     *   request object within client and return clone of
     *   http client with given request
     *
     * - it must replace with current response
     *
     * @param RequestInterface|null $request
     *
     * @return iHttpAgent
     */
    function send(RequestInterface $request = null);

    /**
     * Response
     *
     * - response exists after send any request
     *   it will replaced after each send call
     *
     * @return iHttpResponse|false Request was not send yet
     */
    function response();


    // ..

    /**
     * @return iHAgentOptions
     */
    function options();

    /**
     * Get An Bare Options Instance
     *
     * ! it used on easy access to options instance
     *   before constructing class
     *   [php]
     *      $opt = Filesystem::optionsIns();
     *      $opt->setSomeOption('value');
     *
     *      $class = new Filesystem($opt);
     *   [/php]
     *
     * @return iHAgentOptions
     */
    static function optionsIns();
}
