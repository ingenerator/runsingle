<?php
/**
 * Wrapper script to run a script only once at a time across multiple instances
 *
 * Invoke it:
 *   php run_single.php [--gc=<0|1>] --task_name=<task_name> --timeout=<timeout> --<command>
 *
 * Without any of "--gc=<0|1>", RunSingle will automatically garbage-collect.
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */

use \Ingenerator\RunSingle\RunSingle;

if (is_file($autoload = getcwd() . '/vendor/autoload.php')) {
    require $autoload;
}

$runsingle = \Ingenerator\RunSingle\Factory::create();

$parser = new \Ingenerator\RunSingle\ArgumentParser();
$args = $parser->parse($argv);

$runsingle->execute($args['task_name'], $args['command'], $args['timeout'], $args['automatic_garbage_collect']);
