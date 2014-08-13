#! /usr/bin/env php
<?php
/**
 * Wrapper script to run a script only once at a time across multiple instances
 *
 * Invoke it:
 *   run_single.php [--no-garbage--collect] --task_name=<task_name> --timeout=<timeout> -- <command>
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */

use \Ingenerator\RunSingle\Factory;
use \Ingenerator\RunSingle\ArgumentParser;

error_reporting(E_ALL | E_STRICT);

if (is_file($autoload = getcwd() . '/vendor/autoload.php')) {
    require $autoload;
}

$runsingle = Factory::create();

$parser = new ArgumentParser();
$args = $parser->parse($argv);

$runsingle->execute($args['task_name'], $args['command'], $args['timeout'], $args['automatic_garbage_collect']);
