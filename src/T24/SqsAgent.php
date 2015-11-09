<?php
/**
 * User: eapbachman
 * Date: 08/11/15
 */

namespace T24;


use T24\Handler\ExecutionContext;
use Symfony\Component\EventDispatcher\Event;
use T24\SqsEvents;
use Aws\Sqs\SqsClient;
use T24\Event\SqsMessageReceivedEvent;
use T24\Event\ConfigEvent;

class SqsAgent
{

    /**
     * @var SqsAgentConfig
     */
    protected $config;

    /**
     * @var ExecutionContext
     */
    protected $context;

    function __construct(SqsAgentConfig $config, ExecutionContext $context) {
        $this->config = $config;
        $this->context = $context;

        $event = new ConfigEvent($config);
        $context->getEventDispatcher()->dispatch(SqsEvents::EVENT_SQSAGENT_CONFIGURE, $event);

    }

    function run() {

        $config = $this->config;
        $context = $this->context;

        /* @var $context ExecutionContext */
        $write = function ($s)  {
            echo $s;
        };
        $writeln = function ($s)  {
            $d = new \DateTime();
            echo $d->format(\DateTime::ISO8601) . ' ' . $s . "\n";
        };

        $c = $color = new \Colors\Color();
        /* @var $c \Colors\Color */

        $writeln($c('Running agent')->bold());

        // generate an array with handlers
        $writeln('Loading handlers');
        $handlers = [];
        $handlersDir = $config->handlers_dir;
        if ($handlersDir) {
            $finder = new \Symfony\Component\Finder\Finder();
            foreach ($finder->in($handlersDir)->name('*.php')->sortByName() as $file) {

                /* @var $file \SplFileInfo */
                $handlerId = $file->getBasename('.php');
                $handler = include($file);
                $handlers[$handlerId] = $handler;
                $writeln('  setting up handler ' . $color($handlerId)->bold() . $color->yellow(' (in ' . $file . ')'));
                $context->getEventDispatcher()->addListener(
                    SqsEvents::EVENT_SQSAGENT_SQSMESSAGERECEIVED,
                    function (\T24\Event\SqsMessageReceivedEvent $event) use ($write, $writeln, $color, $handler, $handlerId) {
                        $writeln($color->bg('blue', $color->white('invoking handler ' . $handlerId . ' for event EVENT_SQSAGENT_SQSMESSAGERECEIVED')));
                        /* @var Callable $handler */
                        $result = $handler($event);
                        // write comments
                        $comments = $event->getComments();
                        foreach ($comments as $comment) {
                            $writeln($color('  comment: ')->yellow() . $comment);
                        }
                        $writeln('  - handler ' . $handlerId . ' had been invoked.');
                        if ($event->isPropagationStopped()) {
                            $writeln('  - ' . $color->red('the event was stopped from further propagation.'));

                        } else {
                            $writeln('  - the event was not stopped. proceed to next handler');
                        }

                        return $result;
                    }
                );

            }
            $writeln('Loaded ' . count($handlers) . '  handlers.');
        } else {
            $writeln('default handlers dir not specified, no handlers defined..');
        }

        // @todo: get aws params.
        $region = '';
        $sqsQueueUrl = '';
        $sqs = SqsClient::factory(
            [
                'region' => $config->aws_region,
                'version' => '2012-11-05',
                'credentials' => [
                    'key' => $config->aws_key,
                    'secret' => $config->aws_secret,
                ]
            ]
        );


        // start the process
        $ttl = (int)$config->ttl;
        $ttl = min($ttl, 300);
        $ttl = max($ttl, 5);
        $sleep = (int)$config->sleep;
        $sleep = min($sleep, $ttl - 5);
        $sleep = max($sleep, 0);


        $stopwatch = new \Symfony\Component\Stopwatch\Stopwatch();
        $b = $stopwatch->start('loop');

        while ($b->getDuration() < ($ttl * 1000)) {

            // get an sqs message from the queue
            $writeln($c('Polling Sqs for messages'));
            $result = $sqs->receiveMessage(
                [
                    'QueueUrl' => $config->sqs_queue_url,
                    'AttributeNames' => ['All'],
                    'MessageAttributeNames' => ['All'],
                    'MaxNumberOfMessages' => 1
                ]
            );
            /* @var $result \Aws\Result */
            if (!$result['Messages'] || ($result['Messages']) == 0) {
                $writeln($c('no messages found in sqs queue'));
            } else {
                $writeln($c(sprintf('%d message(s) retrieved from queue.', count($result['Messages']))));
                foreach ($result['Messages'] as $message) {
                    // set up an event that the message is received.
                    // the default subscriber will pass the event to the event handlers in the handlers dir
                    $event = new SqsMessageReceivedEvent($context, $message);
                    $context->getEventDispatcher()->dispatch(SqsEvents::EVENT_SQSAGENT_SQSMESSAGERECEIVED, $event);

                    /*
                    $context->getEventDispatcher()->dispatch(SqsEvents::EVENT_SQSAGENT_SQSMESSAGEHANDLED, $event2);
                    if ($event->isProcessed()) {
                        $context->getEventDispatcher()->dispatch(SqsEvents::EVENT_SQSAGENT_SQSMESSAGEPROCESSED, $event2);
                    }
                    */

                    if ($event->getRemoveFromQueue()) {
                        $writeln('  - the event handlers indicate the sqs message should  be removed from the queue.');
                        // remove from sqs
                        $sqsMessage = $event->getSqsMessage();
                        $removed = $sqs->deleteMessage(
                            [
                                'QueueUrl' => $config->sqs_queue_url,
                                'ReceiptHandle' => $sqsMessage['ReceiptHandle']
                            ]
                        );
                        $writeln('  - ' . ($removed ? $c->green('succesfully removed the message from SQS') : $c->red('failed to remove the message from SQS')));
                    } else {
                        $writeln('  - the event handlers indicate the sqs messageshould not be removed from the queue.');
                    }
                }


            }


            $writeln($color->cyan('sleeping for ' . $sleep . ' seconds'));
            sleep($sleep);

        } // end stopwatch while loop


        $writeln('Stopped after ' . (int)($b->getDuration() / 1000) . ' secs.');

        // send the finish event.
        $event = new \T24\Event\AgentEvent($context);
        $context->getEventDispatcher()->dispatch(SqsEvents::EVENT_SQSAGENT_FINISH, $event);
    }

}