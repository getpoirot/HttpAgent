<?php
namespace Poirot\HttpAgent;

use Poirot\Core\AbstractOptions;
use Poirot\Core\Traits\CloneTrait;
use Poirot\Http\Message\Request\HttpRequestOptionsTrait;

class BrowserRequestOptions extends AbstractOptions
{
    use CloneTrait;
    use HttpRequestOptionsTrait;
}
