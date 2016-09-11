<?php
namespace Poirot\HttpAgent\Transporter;


use Poirot\Connection\Http\OptionsHttpSocket;

class HttpTransporterOptions 
    extends OptionsHttpSocket
{
    /** @var bool Http Transporter Allowed To Decode Body Response */
    protected $allowedDecoding = true;

    
    // ... TODO move outside

    /**
     * note: some times we need raw body from response
     *       without any modification or filters added
     *       it maybe used when we use StreamHttp as a
     *       proxy or want to flush response directly
     *       into output that handle decoding itself.
     *
     * @param boolean $allowedDecoding
     * 
     * @return $this
     */
    function setAllowDecoding($allowedDecoding)
    {
        $this->allowedDecoding = (boolean) $allowedDecoding;
        return $this;
    }

    /**
     * @return boolean
     */
    function isAllowDecoding()
    {
        return $this->allowedDecoding;
    }
}
