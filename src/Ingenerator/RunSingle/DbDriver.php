<?php
/**
 * Database driver
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\RunSingle;

use \Ingenerator\RunSingle\PdoDatabaseObject;
use \Psr\Log\LoggerInterface;

class DbDriver implements LockDriver
{
    /**
     * @var \Ingenerator\RunSingle\PdoDatabaseObject
     */
    protected $db_object;

    /**
     * @var callable
     */
    protected $timeProvider = 'time';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    public function __construct(PdoDatabaseObject $db_object)
    {
        $this->db_object = $db_object;
    }

    /**
     * @param callable $provider
     */
    public function set_time_provider($provider)
    {
        $this->timeProvider = $provider;
    }

    /**
     * @return int
     */
    protected function get_time()
    {
        $time = call_user_func($this->timeProvider);
        return $time;
    }

    /**
     * @param  string $task_name
     * @param  int    $timeout
     * @param string  $lock_holder
     *
     * @return false|integer
     * @throws \Exception
     * @throws \PDOException
     */
    public function get_lock($task_name, $timeout, $lock_holder)
    {
        $timestamp = $this->get_time();

        try {
            $this->db_object->execute('INSERT INTO '.$this->db_object->get_db_table_name()." VALUES(:task_name, :timestamp, :timeout, :lock_holder)", array(
                ':task_name'   => $task_name,
                ':timestamp'   => $timestamp,
                ':timeout'     => $timeout,
                ':lock_holder' => $lock_holder,
            ));
        } catch (\PDOException $e) {
            if (substr($e->getMessage(), 0, 69) === 'SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry') {
                return FALSE;
            } else {
                throw $e;
            }
        }

        return $timestamp;
    }

    /**
     * @param string $task_name
     *
     * @return void
     */
    public function garbage_collect($task_name)
    {
        $result = $this->db_object->fetch_all('SELECT * FROM '.$this->db_object->get_db_table_name().' WHERE task_name = :task_name AND (lock_timestamp + timeout) < :current_timestamp', array(
            ':task_name'         => $task_name,
            ':current_timestamp' => $this->get_time()
        ));
        if (count($result) === 0) {
            $this->log('debug', 'no stale locks found for '.$task_name);
            return;
        }
        $this->log('notice', 'lock found for '.$task_name);
        $this->release_lock($result[0]['task_name'], $result[0]['lock_timestamp']);
    }

    /**
     * @param string $task_name
     * @param int    $lock_timestamp
     *
     * @return void
     */
    public function release_lock($task_name, $lock_timestamp)
    {
        $this->log('debug', 'releasing lock for task '.$task_name);
        $this->db_object->execute('DELETE FROM '.$this->db_object->get_db_table_name().' WHERE task_name = :task_name AND lock_timestamp = :lock_timestamp', array(
            ':task_name'      => $task_name,
            ':lock_timestamp' => $lock_timestamp
        ));
    }

    /**
     * @param LoggerInterface $logger
     */
    public function set_logger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log only if logger is set.
     *
     * @param $level
     * @param $message
     */
    protected function log($level, $message)
    {
        if ($this->logger) {
            call_user_func(array($this->logger, $level), $message);
        }
    }

}
