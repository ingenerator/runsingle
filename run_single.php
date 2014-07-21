<?php
/**
 * php run_single.php <task_name> <timeout> --<command>
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */

require_once('RunSingle.php');

$args_string = implode(' ', $argv);

$parsed_args_string = explode(' -- ', $args_string);
$command = $parsed_args_string[1];

$args = explode(' ', $parsed_args_string[0]);

$task_name = $args[1];
$timeout = $argv[2];

$runsingle = new \RunSingle\RunSingle($task_name, $command, $timeout);
$runsingle->execute();
