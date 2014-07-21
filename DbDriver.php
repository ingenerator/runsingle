<?php
/**
 * Database driver
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */

namespace RunSingle;

use \PDO;
require_once('LockDriver.php');
require_once('MySqlConfigData.php');

class DbDriver implements LockDriver {

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var PdoConfigData
     */
    protected $config_object;

    /**
     * @param \PDO $config_object
     */
    public function __construct($config_object)
    {
        $this->config_object = $config_object;
    }

    public function init()
    {
        $this->pdo = $this->config_object->get_pdo();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $db_name = "`".str_replace("`","``", $this->config_object->get_db_name())."`";
        $this->pdo->query("CREATE DATABASE IF NOT EXISTS $db_name");
        $this->pdo->query("use $db_name");
        $this->pdo->query("CREATE TABLE IF NOT EXISTS locks(
                      task_name varchar(255),
                      last_lock int,
                      timeout int,
                      PRIMARY KEY (task_name)
                    )");
    }

    /**
     * @param  string    $task_name
     * @param  int       $timeout
     * @return bool
     */
    public function get_lock($task_name, $timeout)
    {
        $this->garbage_collect($task_name, $timeout);
        $sql = "SELECT * FROM locks WHERE task_name = :task_name";
        $q = $this->pdo->prepare($sql);
        $q->bindParam(':task_name', $task_name);
        $q->execute();
        $result = $q->fetchAll();

        if(count($result) === 1){
            return FALSE;
        } else {
            $datetime = new \DateTime();
            $timestamp = $datetime->getTimestamp();

            $sql = "INSERT INTO locks VALUES(:task_name, :timestamp, :timeout)";
            $q = $this->pdo->prepare($sql);
            $q->bindParam(':task_name', $task_name);
            $q->bindParam(':timeout', $timeout);
            $q->bindParam(':timestamp', $timestamp);
            $q->execute();

            return TRUE;
        }
    }

    /**
     * @param string $task_name
     * @param int    $timeout
     */
    public function garbage_collect($task_name, $timeout)
    {
        $datetime = new \DateTime();
        $datetime_string = $datetime->format('d/m/Y H:i:s');

        echo "$datetime_string: task_name: $task_name; Running garbage collection ... ";
        $time_since_last_run = $this->get_time_since_last_run($task_name);

        if ($time_since_last_run > $timeout){
            $this->release_lock($task_name);
            return;
        }

        $diff = abs($time_since_last_run - $timeout);
        echo "still locked for $diff seconds.\n";
    }

    /**
     * @param string $task_name
     * @return int
     */
    protected function get_time_since_last_run($task_name)
    {
        $datetime = new \DateTime();
        $timestamp = $datetime->getTimestamp();

        $sql = "SELECT * FROM locks WHERE task_name = :task_name";
        $q = $this->pdo->prepare($sql);
        $q->bindParam(':task_name', $task_name);
        $q->execute();
        $result = $q->fetchAll();
        $timestamp_file = '';
        if(count($result) === 1){
            $timestamp_file = $result[0]['last_lock'];
        }

        if ($timestamp_file === '') {
            $timestamp_file = 0;
        }

        $time_since_last_run = $timestamp - $timestamp_file;
        return $time_since_last_run;
    }

    /**
     * @param $task_name
     */
    public function release_lock($task_name)
    {
        $sql = "DELETE FROM locks WHERE task_name = :task_name";
        $q = $this->pdo->prepare($sql);
        $q->bindParam(':task_name', $task_name);
        $q->execute();
        echo "released lock.\n";
    }

}
