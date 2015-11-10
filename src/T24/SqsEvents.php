<?php
/**
 * User: eapbachman
 * Date: 08/11/15
 */

namespace T24;


final class SqsEvents
{

    const EVENT_SQSAGENT_CONFIGURE = 'sqsagent.configure';
    const EVENT_SQSAGENT_FINISH = 'sqsagent.finish';
    const EVENT_SQSAGENT_SQSMESSAGERECEIVED = 'sqsagent.message_received';
    //const EVENT_SQSAGENT_SQSMESSAGEHANDLED = 'sqsagent.message_handled';
    //const EVENT_SQSAGENT_SQSMESSAGEPROCESSED = 'sqsagent.message_processed';

    static function generateEventForAgentId($eventId,$agentId) {

        return str_replace('sqsagent.', 'sqsagent.' . $agentId . '.', $eventId);


    }

}