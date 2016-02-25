<?php
namespace Poirot\HttpAgent;

use Poirot\Std\Struct\AbstractOptionsData;
use Poirot\Std\Traits\CloneTrait;
use Poirot\Http\Message\Request\HttpRequestOptionsTrait;

class BrowserRequestOptions extends AbstractOptionsData
{
    use CloneTrait;
    use HttpRequestOptionsTrait;
}
