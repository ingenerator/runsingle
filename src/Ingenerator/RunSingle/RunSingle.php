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
     * @var LockHolder
     */
    protected $lock_holder;

    /**
     * @param LockDriver       $driver
     * @param CommandRunner    $runner
     * @param LoggerInterface  $logger
     * @param LockHolder $lock_holder
     */
    public function __construct(
        LockDriver $driver,
        CommandRunner $runner,
        LoggerInterface $logger,
        LockHolder $lock_holder
    ) {
        $this->driver      = $driver;
        $this->runner      = $runner;
        $this->logger      = $logger;
        $this->lock_holder = $lock_holder;
//        //print_r($lock_holder->get_lock_holder(), 1);
//        echo $lock_holder->get_lock_holder();
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

        $this->logger->info('lock_holder id is '.$this->lock_holder->get_lock_holder());
        $this->logger->info('trying to get lock for task '.$task_name);
        $lock_id = $this->driver->get_lock($task_name, $timeout, $this->lock_holder->get_lock_holder());
        if ($lock_id !== FALSE) {
            $this->logger->info('executing task '.$task_name.' ...');
            $this->logger->info('<command output>');

            $start_time = \time();
            $exit_code  = $this->runner->execute($command);
            $end_time   = \time();

            $elapsed_time = $end_time - $start_time;
            $this->logger->info('</command output>');
            $this->logger->info('finished '.$task_name.' after '.$elapsed_time.' seconds with exit code '.$exit_code);

            $this->driver->release_lock($task_name, $lock_id);
            return $exit_code;
        }

        $this->logger->info('no lock available for '.$task_name);
        return 0;
    }

}
