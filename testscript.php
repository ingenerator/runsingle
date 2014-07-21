<?php
/**
 * Testscript
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */


$datetime = new \DateTime();
$datetime_string = $datetime->format('d/m/Y H:i:s');
$task_name = $argv[1];
$timeout = $argv[2];
$instance_number = $argv[3];
echo "\n$datetime_string: running $task_name; timeout: $timeout on instance #$instance_number ...\n";
