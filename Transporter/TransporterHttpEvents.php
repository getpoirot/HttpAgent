<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Events\BaseEvent;
use Poirot\Events\BaseEvents;
use Poirot\Events\EventBuilder;

class TransporterHttpEvents extends BaseEvents
{
    const EVENT_REQUEST_SEND_PREPARE      = 'request.send.prepare';
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

        $this->setCollector(new TransporterHttpEventCollector);

        // attach default event names:
        ## also share this collector into them
        $this->bindShare(new BaseEvent(self::EVENT_REQUEST_SEND_PREPARE));
        $this->bindShare(new BaseEvent(self::EVENT_RESPONSE_HEADERS_RECEIVED));
        $this->bindShare(new BaseEvent(self::EVENT_RESPONSE_BODY_RECEIVED));
    }
}
