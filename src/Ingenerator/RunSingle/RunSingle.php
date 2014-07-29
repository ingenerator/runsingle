<?php
/**
 * Wrapper script to run a command not more than once at a time across multiple instances.
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */

namespace Ingenerator\RunSingle;

class RunSingle {

    /**
     * @var string
     */
    protected $config_file = 'run_single_config.php';

    /**
     * @var \Ingenerator\RunSingle\DbDriver
     */
    protected $driver;

    /**
     * @var \Ingenerator\RunSingle\CommandRunner $runner
     */
    protected $runner;

    public function __construct($driver, $runner)
    {
        $this->driver = $driver;
        $this->runner = $runner;
    }

    public function execute($task_name, $command, $timeout, $garbage_collect)
    {
        $this->driver->init();
        $lock_timestamp = $this->driver->get_lock($task_name, $timeout, $garbage_collect);
        if($lock_timestamp !== FALSE)
        {
            $exit_code = $this->runner->execute($command);
            $this->driver->release_lock($task_name, $lock_timestamp);
            return $exit_code;
        }

        return 0;
    }

}
