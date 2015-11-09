<?php
/**
 * User: eapbachman
 * Date: 07/11/15
 */

namespace T24\Event;

use T24\Handler\ExecutionContext;

class SqsMessageReceivedEvent extends AbstractMessageReceivedEvent {


    protected $sqsMessage = null;

    protected $message = null;



    function __construct(ExecutionContext $context, $sqsMessage)
    {
        parent::__construct($context);
        $this->sqsMessage = $sqsMessage;
        $this->message = json_decode($this->sqsMessage['Body'], true);
    }


    function getSqsMessage()
    {
        return $this->sqsMessage;
    }

    function getMessage()
    {
        return $this->message;
    }

    function describe() {
        $lines = explode("\n", $this->sqsMessage['Body']);
        return $this->sqsMessage['MessageId'] . ' ' . substr($lines[0], 0, 512);
    }


}