<?php
/**
 * User: eapbachman
 * Date: 08/11/15
 */

namespace T24\Context;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class EventContext
 * Execution
 * @package T24
 */
class ExecutionContext extends \ArrayObject
{

    protected $eventDispatcher =  null;

    function __construct() {

    }

    /**
     * @return ExecutionContext
     */
    static function create() {
        $context = new self();
        $context->eventDispatcher = new EventDispatcher();
        return $context;
    }

    /**
     * Get the EventDispatcher
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Set the eventDispatcher
     * @param null $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }



}