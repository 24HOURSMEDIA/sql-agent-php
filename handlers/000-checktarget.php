<?php
/**
 * Checks if a message is targeted at this specific ec2 instance
 */
/*
{
// sample evcent
  message_version: '1.0',
  event_source: 'aws:lambda',
  type: 'event',
  event_id: 'backend_dns_changed',
  target_type: 'ec2',
  target_instance: 'i-3bc63696',
  event_data:
  {


   }
   }
*/
use T24\Event\AbstractMessageReceivedEvent;

return function (AbstractMessageReceivedEvent $event) use ($color) {

    $event->addComment('testing if the event is targeted at this ec2 instance.');
    // check if target_instance is the current instance
    $message = $event->getMessage();



    $thisInstanceId = 'i-3bc63696';
    if (isset($message['target_type'] , $message['target_instance']) && $message['target_type'] == 'ec2') {
        if ($message['target_instance'] != $thisInstanceId) {
            $event->addComment(
                $color->red(sprintf('the message was intended for instance %s but this is %s.', $message['target_instance'], $thisInstanceId))
            );
            $event->stopPropagation();
            return;
        }
        $event->addComment($color->green(sprintf('the message was intended this instance: %s.', $message['target_instance'])));
    }
};
