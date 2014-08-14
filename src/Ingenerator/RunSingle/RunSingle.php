<?php
/**
 * Wrapper script to run a command not more than once at a time across multiple instances.
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\RunSingle;

class RunSingle
{
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

    /**
     * @param \Ingenerator\RunSingle\DbDriver      $driver
     * @param \Ingenerator\RunSingle\CommandRunner $runner
     */
    public function __construct(DbDriver $driver, CommandRunner $runner)
    {
        $this->driver = $driver;
        $this->runner = $runner;
    }

    /**
     * @param string $task_name
     * @param string $command
     * @param string $timeout
     * @param string $garbage_collect
     *
     * @return integer
     */
    public function execute($task_name, $command, $timeout, $garbage_collect)
    {
        $this->driver->init();
        $lock_id = $this->driver->get_lock($task_name, $timeout, $garbage_collect);
        if ($lock_id !== FALSE) {
            $exit_code = $this->runner->execute($command);
            $this->driver->release_lock($task_name, $lock_id);
            return $exit_code;
        }

        return 0;
    }

}
