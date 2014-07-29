<?php
/**
 * Database driver
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */

namespace Ingenerator\RunSingle;

use \Ingenerator\RunSingle\PdoDatabaseObject;

class DbDriver implements LockDriver {

    /**
     * @var \Ingenerator\RunSingle\PdoDatabaseObject
     */
    protected $db_object;

    /**
     * @param \Ingenerator\RunSingle\PdoDatabaseObject $db_object
     */
    public function __construct($db_object)
    {
        $this->db_object = $db_object;
        $this->timeProvider = 'time';
    }

    public function set_time_provider(callable $provider)
    {
        $this->timeProvider = $provider;
    }

    protected function get_time()
    {
        $time = call_user_func($this->timeProvider);
        return $time;
    }

    public function init()
    {
        $this->db_object->init();
    }

    /**
     * @param  string            $task_name
     * @param  int               $timeout
     * @param  bool              $garbage_collect
     * @return bool|int
     * @throws \Exception
     * @throws \PDOException
     */
    public function get_lock($task_name, $timeout, $garbage_collect)
    {
        $timestamp = $this->get_time();

        if ($garbage_collect === 'TRUE' or $garbage_collect === TRUE) {
            $this->garbage_collect($task_name, $timeout);
        }

        try {
            $this->db_object->execute("INSERT INTO " . $this->db_object->get_db_table_name() . " VALUES(:task_name, :timestamp, :timeout)", array(':task_name' => $task_name, ':timestamp' => $timestamp, ':timeout' => $timeout));
        } catch (\PDOException $e) {
            if (substr($e->getMessage(), 0, 69) === "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry") {
                echo "duplicate key\n";
                return FALSE;
            } else {
                throw $e;
            }
        }

        return $timestamp;
    }

    /**
     * @param string $task_name
     * @param int    $timeout
     */
    public function garbage_collect($task_name, $timeout)
    {
        $result = $this->db_object->fetch_all('SELECT * FROM ' . $this->db_object->get_db_table_name() . ' WHERE task_name = :task_name AND (lock_timestamp + timeout) < :current_timestamp', array(':task_name' => $task_name, ':current_timestamp' => $this->get_time()));
        if (count($result) === 0) return;
        $this->release_lock($result[0]['task_name'], $result[0]['lock_timestamp']);
    }

    /**
     * @param string $task_name
     * @param int $lock_timestamp
     */
    public function release_lock($task_name, $lock_timestamp)
    {
        $this->db_object->execute("DELETE FROM " . $this->db_object->get_db_table_name() . " WHERE task_name = :task_name AND lock_timestamp = :lock_timestamp", array(':task_name' => $task_name, ':lock_timestamp' => $lock_timestamp));
    }

}

