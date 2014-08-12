<?php
/**
 * LockDriver interface
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\RunSingle;

interface LockDriver
{
    /**
     * Get the lock for the task if none exists already.
     *
     * @param string $task_name
     * @param int    $timeout
     * @param bool   $garbage_collect
     *
     * @return bool|int
     */
    public function get_lock($task_name, $timeout, $garbage_collect);

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

}
