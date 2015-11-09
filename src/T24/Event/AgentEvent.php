<?php
/**
 * User: eapbachman
 * Date: 08/11/15
 */

namespace T24\Event;


use Symfony\Component\EventDispatcher\Event;
use T24\Handler\ExecutionContext;

class AgentEvent extends Event
{

    protected $params = [];


    protected $context;

    function __construct(ExecutionContext $context)
    {
        $this->context = $context;
    }

    function setParam($key, $val)
    {
        $this->params[$key] = $val;

        return $this;
    }

    function getParam($key)
    {
        return $this->params[$key];
    }

    function hasParam($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * @return ExecutionContext
     */
    function getContext()
    {
        return $this->context;
    }

}