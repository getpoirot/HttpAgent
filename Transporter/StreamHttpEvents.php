<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Events\BaseEvent;
use Poirot\Events\BaseEvents;
use Poirot\Events\EventBuilder;

class StreamHttpEvents extends BaseEvents
{
    const EVENT_RESPONSE_HEAD_READ = 'response.head.read';

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

        // attach default event names:
        $this->bind(new BaseEvent(self::EVENT_RESPONSE_HEAD_READ));
    }
}
