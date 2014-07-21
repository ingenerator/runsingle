<?php
/**
 * Simulate non-deterministic behaviour of multiple instances (on different machines) calling a locking script with varying delay.
 *
 * Run it:
 *     php run_single.php <task_name> <timeout> <instance_number> --<command>
 *
 * To run multiple instances via cron:
 *     Run crontab -e
 *     Then add the following lines:
 *
 *     * * * * * php ./run_testscript.php 3 testscript 30 1 -- php testscript.php >> testscript.log
 *     * * * * * php ./run_testscript.php 3 testscript 30 2 -- php testscript.php >> testscript.log
 *     * * * * * php ./run_testscript.php 3 testscript 30 3 -- php testscript.php >> testscript.log
 *
 * testscript.log should show varying "instances" getting the lock.
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */

require_once('RunSingle.php');

$sleep_max = 3;

$args_string = implode(' ', $argv);

$parsed_args_string = explode(' -- ', $args_string);
$command = $parsed_args_string[1];

$args = explode(' ', $parsed_args_string[0]);

$sleep_max = $args[1] * 1000000;
$sleep_microsecs = rand(1, $sleep_max);
$sleep_secs = (float)$sleep_microsecs/1000000;

$task_name = $args[2];

$timeout = $argv[3];

$instance_number = $args[4];

$new_command = $command . ' ' . $task_name. ' ' . $timeout .' ' . $instance_number . '  >> ' . $task_name .'.log &';
$runsingle = new \RunSingle\RunSingle($task_name, $new_command, $timeout);

$datetime = new DateTime();
$datetime_string = $datetime->format('d/m/Y H:i:s');

usleep($sleep_microsecs);
$runsingle->execute();
