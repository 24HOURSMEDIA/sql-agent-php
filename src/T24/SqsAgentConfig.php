<?php
/**
 * User: eapbachman
 * Date: 09/11/15
 */

namespace T24;

/**
 * Class SqsAgentConfig
 *
 * Configuration object for the SQS Agent
 * Gets populated through events
 *
 * @package T24
 */
class SqsAgentConfig
{

    /**
     * Agent name (Appears in logs etc).
     * You can have several agents running, by giving them different names you
     * differentiate
     * @var string
     */
    public $agent_name = 'default-sqs-agent';

    /**
     * AWS Credential
     * @var stromg
     */
    public $aws_key;
    /**
     * AWS Credential
     * @var stromg
     */
    public $aws_secret;
    /**
     * AWS Region of the queue
     * @var stromg
     */
    public $aws_region;

    /**
     * The SQS queue url to poll from
     * @var
     */
    public $sqs_queue_url;

    /**
     * Base dir to resolve directories from
     * @var
     */
    public $base_dir;

    /**
     * Time for the agent to run in seconds
     * @var int
     */
    public $ttl = 45;

    /**
     * Number of seconds to sleep after each poll
     * @var int
     */
    public $sleep = 3;


}