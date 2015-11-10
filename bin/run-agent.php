<?php
/**
 * runs the agent
 */

require __DIR__ . '/../vendor/autoload.php';
use T24\Context\ExecutionContext;
use T24\SqsEvents;
use T24\SqsAgent;
use T24\SqsAgentConfig;
use T24\Event\ConfigEvent;
// first define the execution context of the command
// this primarily contains an event dispatcher for hooking into the command's execution flow
// you can specify your own execution context by wrapping this script in a script that defines a custom context.
// for example, you can set a DIC in the context that then becomes available to event handlers through $event->getContext()['container']
// i.e. prototypic to inject a symfony container and event dispatcher in the context:
// <?php
// $context = T24\Context\ExecutionContext::create();
// $context['container'] = $app->getContainer();
// $context->setEventDispatcher($app->getContainer()->get['event_dispatcher']);
// require('run-agent.php');
//
$context = isset($context) ? $context : ExecutionContext::create();
if (!$context instanceof ExecutionContext) {
    throw new RuntimeException('Execution context is not an instance of T24\Handler\ExecutionContext but ' . get_class($context));
}





// php file.php ./assets/example.txt
ini_set('display_errors', 1);
$cmd = new Commando\Command();
$cmd->setHelp('sqs agent')
    ->option('ttl')
   // ->defaultsTo(45)
    ->describedAs('sets the ttl / runtime of the script. Defaults to 45 (in seconds)')
    ->option('sleep')
    ->defaultsTo(null)
    ->describedAs('sets the time to rest between two message retrievals. Defaults to 3 (in seconds)')
    ->option('config')
    ->defaultsTo(null)
    ->describedAs('configuration file')

;

$run = function () use ($cmd, $context) {

    $_options = $cmd->getOptions();
    $options = [];
    foreach ($_options as $key => $option) {
        $options[$key] = $option->getValue();
    }
    $options['base_dir'] = __DIR__ . '/../';

    // for this binary, subscribe to some configuration events to load config files etc.

    // first, load the configuration file
    $context->getEventDispatcher()->addListener(SqsEvents::EVENT_SQSAGENT_CONFIGURE, function(ConfigEvent $event) use ($options) {
        if ($options['config']) {
            $newCfg = json_decode(file_get_contents($options['config']), true);
            if (!$newCfg) {
                throw new RuntimeException('could not load config file ' . $options['config']);
            }
            $event->getConfig()->merge($newCfg);
        }
    });

    // then, overwrite configuration options from the command line arguments
    $context->getEventDispatcher()->addListener(SqsEvents::EVENT_SQSAGENT_CONFIGURE, function(ConfigEvent $event) use ($options) {
        $c = $event->getConfig();
        if ($options['ttl']) {
            $c->ttl = $options['ttl'];
        }
        if ($options['sleep']) {
            $c->ttl = $options['ttl'];
        }
    });




    // configure with default configuration, and events.
    $config = new SqsAgentConfig();

    $event = new ConfigEvent($config);
    $context->getEventDispatcher()->dispatch(SqsEvents::EVENT_SQSAGENT_CONFIGURE, $event);
    // check if the config has an id for the agent
    if (!$config->agent_id) {
        throw new \RuntimeException('The configuration MUST specify an agent id, none given');
    }

    $agent = new SqsAgent($config, $context);
    $agent->run();
};

$run();