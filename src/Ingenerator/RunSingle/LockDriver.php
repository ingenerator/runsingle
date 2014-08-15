<?php
/**
 * LockDriver interface
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\RunSingle;

use \Psr\Log\LoggerInterface;

interface LockDriver
{
    /**
     * Get the lock for the task if none exists already.
     *
     * @param string $task_name
     * @param int    $timeout
     * @param string $lock_holder
     *
     * @return bool|int
     */
    public function get_lock($task_name, $timeout, $lock_holder);

    /**
     * Garbage collect stale entries in the lock storage.
     *
     * @param string $task_name
     *
     * @return void
     */
    public function garbage_collect($task_name);

    /**
     * Release a lock.
     *
     * @param string $task_name
     * @param int    $lock_id
     *
     * @return void
     */
    public function release_lock($task_name, $lock_id);

    /**
     * Set logger.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function set_logger(LoggerInterface $logger);

    /**
     * Return a list of locks.
     *
     * @return Lock[]
     */
    public function list_locks();

}
