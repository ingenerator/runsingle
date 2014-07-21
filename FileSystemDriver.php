<?php
/**
 *
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */

namespace RunSingle;

require_once('LockDriver.php');

class FileSystemDriver implements LockDriver {

    public function get_lock($task_name, $timeout)
    {
        $this->garbage_collect($task_name, $timeout);
        if(count(glob($this->lock_file_name($task_name))) === 1){
            return FALSE;
        } else {
            $datetime = new \DateTime();
            $timestamp = $datetime->getTimestamp();

            $handle = fopen($this->lock_file_name($task_name), 'w');
            fwrite($handle, $timestamp);
            fclose($handle);

            return TRUE;
        }
    }

    public function garbage_collect($task_name, $timeout)
    {
        echo "    (using file system) Running garbage collection ...\n";
        $time_since_last_run = $this->get_time_since_last_run($task_name);

        if ($time_since_last_run > $timeout){
            $this->release_lock($task_name);
            return;
        }

        $diff = abs($time_since_last_run - $timeout);
        echo "    (using file system) Still locked for $diff seconds.\n\n";
    }

    protected function get_time_since_last_run($task_name)
    {
        $datetime = new \DateTime();
        $timestamp = $datetime->getTimestamp();

        if(!file_exists($this->lock_file_name($task_name))){
            return $timestamp;
        }

        $handle = fopen($this->lock_file_name($task_name), 'r');
        $timestamp_file = fgets($handle);

        if ($timestamp_file === '') {
            $timestamp_file = 0;
        }
        fclose($handle);

        $time_since_last_run = $timestamp - $timestamp_file;
        return $time_since_last_run;
    }

    public function release_lock($task_name)
    {
        echo "    (using file system) Releasing lock ...\n";
        unlink($this->lock_file_name($task_name));
    }

    protected function lock_file_name($task_name){
        return ($task_name . '.lock');
    }
}
