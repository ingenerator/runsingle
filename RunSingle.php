<?php
/**
 * Wrapper script to run a command not more than once every <timeout> seconds across multiple instances.
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */

namespace RunSingle;

require_once('Config.php');

class RunSingle {

    protected $command;
    protected $config;
    protected $task_name;
    protected $timeout = 10;

    public function __construct($task_name, $command, $timeout)
    {
        $this->config = new Config;
        $this->task_name = $task_name;
        $this->timeout = $timeout;
        $this->command = $command;
    }

    public function execute()
    {
        $this->config->init();
        $has_lock = $this->config->get_driver()->get_lock($this->task_name, $this->timeout);
        if($has_lock === TRUE)
        {
            exec($this->command);
        }
    }

}
