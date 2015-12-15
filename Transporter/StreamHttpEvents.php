<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Events\BaseEvent;
use Poirot\Events\BaseEvents;
use Poirot\Events\EventBuilder;
use Poirot\HttpAgent\Transporter\Listeners\StreamHttpEventCollector;

class StreamHttpEvents extends BaseEvents
{
    const EVENT_RESPONSE_HEADERS_RECEIVED = 'response.head.receive';
    const EVENT_RESPONSE_BODY_RECEIVED    = 'response.body.receive';

    /**
     * Construct
     *
     * - new Events('event-name')
     * with setter:
     * - new Events(new EventBuilder([ ...options]))
     *
     * @param EventBuilder|string $setter
     */
    function __construct($setter = null)
    {
        parent::__construct($setter);

        $this->setCollector(new StreamHttpEventCollector);

        // attach default event names:
        ## also share this collector into them
        $this->bindShare(new BaseEvent(self::EVENT_RESPONSE_HEADERS_RECEIVED));
        $this->bindShare(new BaseEvent(self::EVENT_RESPONSE_BODY_RECEIVED));
    }
}
