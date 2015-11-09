<?php
use T24\Event\AbstractMessageReceivedEvent;

/**
 * Restarts nginx when an event message is polled from sqs with the event_id 'backend_dns_changed'
 */
return function(AbstractMessageReceivedEvent $event) use ($color)  {

    $message = $event->getMessage();


    if ($message['type'] == 'event' && in_array($message['event_id'], [
            // legacy
            'backend_dns_changed'
        ])) {
        $event->addComment($color->green(sprintf('event %s can be processed.', $message['event_id'])));
        $event->addComment($color->yellow('action to take: restart nginx'));

        $success = true;
        $output = [];
        $r = exec('nginx restart', $output, $err);
        if ($err) {
            $event->addComment($color->red('nginx not restarted. exit code: ' . $err . '; ' . $r . '; ' . implode(',', $output)));
            $success = false;
        } else {
            $event->addComment($color->green('nginx restarted.'));
        }
        // mark the message as processed. this will set a success status and mark the sqs message to be removed from the queue
        // (after all other handlers and subscribers have been invoked)
        // (or event->stopPropagation() is called).
        $event->processed($success);
    }

};
