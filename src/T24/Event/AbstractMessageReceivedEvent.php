<?php
/**
 * User: eapbachman
 * Date: 09/11/15
 */

namespace T24\Event;


abstract class AbstractMessageReceivedEvent extends AgentEvent
{
    protected $success = false;
    protected $removeFromQueue = false;
    protected $comments = [];
    protected $processed;

    function processed($success = true)
    {
        $this->processed = true;
        $this->success = $success;
        $this->stopPropagation();
        $this->removeFromQueue = true;

        return $this;
    }


    function isProcessed()
    {
        return $this->processed;
    }

    function getSuccess()
    {
        return $this->success;
    }

    function describe()
    {
        return 'message';
    }

    /**
     * Get the RemoveFromSqs
     * @return boolean
     */
    public function getRemoveFromQueue()
    {
        return $this->removeFromQueue;
    }

    public function getComments($purge = true)
    {
        $c = $this->comments;
        $this->comments = [];

        return $c;
    }

    public function addComment($s)
    {
        $this->comments[] = $s;

        return $this;
    }

    abstract function getMessage();

}