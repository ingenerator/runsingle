<?php
/**
 * Wrapper script to run a command not more than once at a time across multiple instances.
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\RunSingle;

use Psr\Log\LoggerInterface;

class RunSingle
{
    /**
     * @var string
     */
    protected $config_file = 'run_single_config.php';

    /**
     * @var \Ingenerator\RunSingle\LockDriver
     */
    protected $driver;

    /**
     * @var \Ingenerator\RunSingle\CommandRunner $runner
     */
    protected $runner;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LockDriver      $driver
     * @param CommandRunner   $runner
     * @param LoggerInterface $logger
     */
    public function __construct(LockDriver $driver, CommandRunner $runner, LoggerInterface $logger)
    {
        $this->driver = $driver;
        $this->runner = $runner;
        $this->logger = $logger;
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
        if ($garbage_collect === TRUE) {
            $this->logger->info('garbage collecting for task '.$task_name);
            $this->driver->garbage_collect($task_name);
        }

        $this->logger->info('trying to get lock for task '. $task_name);
        $lock_id = $this->driver->get_lock($task_name, $timeout, $garbage_collect);
        if ($lock_id !== FALSE) {
            $this->logger->info('executing task '.$task_name.' ...');
            $this->logger->info('<command output>');

            $start_time = time();
            $exit_code = $this->runner->execute($command);
            $end_time = time();

            $elapsed_time = $end_time - $start_time;
            $this->logger->info('</command output>');
            $this->logger->info('finished '.$task_name.' after '.$elapsed_time.' seconds with exit code '.$exit_code);

            $this->driver->release_lock($task_name, $lock_id);
            return $exit_code;
        }

        return 0;
    }

}
