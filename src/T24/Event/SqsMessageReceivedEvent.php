<?php
/**
 * User: eapbachman
 * Date: 07/11/15
 */

namespace T24\Event;

use T24\Handler\ExecutionContext;

class SqsMessageReceivedEvent extends AgentEvent
{
    protected $success = false;

    protected $sqsMessage = null;
    protected $removeFromSqs = false;
    protected $comments = [];
    protected $context;
    protected $processed;

    function __construct(ExecutionContext $context, $sqsMessage) {
        $this->sqsMessage = $sqsMessage;
        $this->context = $context;
    }

    function processed($success = true) {
        $this->processed = true;
        $this->success = $success;
        $this->stopPropagation();
        $this->removeFromSqs = true;
        return $this;
    }




    function isProcessed() {
        return $this->processed;
    }

    function getSuccess() {
        return $this->success;
    }

    function getSqsMessage() {
        return $this->sqsMessage;
    }

    function describe() {
        $lines = explode("\n", $this->sqsMessage['Body']);
        return $this->sqsMessage['MessageId'] . ' ' . substr($lines[0], 0, 512);
    }

    /**
     * Get the RemoveFromSqs
     * @return boolean
     */
    public function getRemoveFromSqs()
    {
        return $this->removeFromSqs;
    }

    public function getComments($purge = true) {
        $c = $this->comments;
        $this->comments = [];
        return $c;
    }

    public function addComment($s) {
        $this->comments[] = $s;
        return $this;
    }
}