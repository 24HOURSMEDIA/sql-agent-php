<?php
/**
 * User: eapbachman
 * Date: 09/11/15
 */

namespace T24\Event;


use Symfony\Component\EventDispatcher\Event;
use T24\SqsAgentConfig;

class ConfigEvent extends Event
{

    /**
     * @var SqsAgentConfig
     */
    protected $config;

    function __construct(SqsAgentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Get the Config
     * @return SqsAgentConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

}