<?php
/**
 * runs the agent
 */

require __DIR__ . '/../vendor/autoload.php';
use T24\Handler\ExecutionContext;
use T24\SqsEvents;
use T24\SqsAgent;

// first define the execution context of the command
// this primarily contains an event dispatcher for hooking into the command's execution flow
// you can specify your own execution context by wrapping this script in a script that defines a custom context.
// for example, you can set a DIC in the context that then becomes available to event handlers through $event->getContext()['container']
// i.e. prototypic to inject a symfony container and event dispatcher in the context:
// <?php
// $context = T24\Handler\ExecutionContext::create();
// $context['container'] = $app->getContainer();
// $context->setEventDispatcher($app->getContainer()->get['event_dispatcher']);
// require('run-agent.php');
//
$context = isset($context) ? $context : ExecutionContext::create();
if (!$context instanceof ExecutionContext) {
    throw new RuntimeException('Execution context is not an instance of T24\Handler\ExecutionContext but ' . get_class($context));
}


use \Aws\Sqs\SqsClient;

// php file.php ./assets/example.txt
ini_set('display_errors', 1);
$cmd = new Commando\Command();
$cmd->setHelp('sqs agent')
    ->option('ttl')
    ->defaultsTo(45)
    ->describedAs('sets the ttl / runtime of the script. Defaults to 45 (in seconds)')
    ->option('sleep')
    ->defaultsTo(3)
    ->describedAs('sets the time to rest between two message retrievals. Defaults to 3 (in seconds)');

$run = function () use ($cmd, $context) {

    $_options = $cmd->getOptions();
    $options = [];
    foreach ($_options as $key => $option) {
        $options[$key] = $option->getValue();
    }
    $options['base_dir'] = __DIR__ . '/../';

    // send the configure event. cmd options are passed in the event and may be modified there (i.e. ttl, sleep...)
    $event = new \T24\Event\AgentEvent($context);
    $event->setParam('options', $options);
    $context->getEventDispatcher()->dispatch(SqsEvents::EVENT_SQSAGENT_CONFIGURE, $event);
    $options = $event->getParam('options');
    $agent = new SqsAgent($options, $context);
    $agent->run();
};

$run();