<?php
namespace Poirot\HttpAgent\Transporter;

use Poirot\Events\Event;
use Poirot\Events\Event\BuildEvent;
use Poirot\Events\EventHeap;


class TransporterHttpEvents 
    extends EventHeap
{
    const EVENT_REQUEST_PREPARE_EXPRESSION = 'request.send.prepare';
    const EVENT_RESPONSE_HEADERS_RECEIVED  = 'response.head.receive';
    const EVENT_RESPONSE_BODY_RECEIVED     = 'response.body.receive';

    
    /**
     * Construct
     *
     * - new Events('event-name')
     * with setter:
     * - new Events(new EventBuilder([ ...options]))
     *
     * @param BuildEvent|string $setter
     */
    function __construct($setter = null)
    {
        parent::__construct($setter);

        $this->collector(new DataCollectorEventOfTransporterHttp);

        // attach default event names:
        ## also share this collector into them
        $this->bindShare( new Event(self::EVENT_REQUEST_PREPARE_EXPRESSION) );
        $this->bindShare( new Event(self::EVENT_RESPONSE_HEADERS_RECEIVED) );
        $this->bindShare( new Event(self::EVENT_RESPONSE_BODY_RECEIVED) );
    }
}
