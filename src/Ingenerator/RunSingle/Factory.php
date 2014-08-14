<?php
/**
 * Factory to create RunSingle object
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */


namespace Ingenerator\RunSingle;

use \Ingenerator\RunSingle\PdoDatabaseObject;

class Factory
{

    const DB_DRIVER_FACTORY_FILE = 'run_single_config.php';

    public static function create()
    {
        $driver_factory_file_path = realpath('./').'/'.self::DB_DRIVER_FACTORY_FILE;
        if (! file_exists($driver_factory_file_path)) {
            throw new \Exception('Please create '.$driver_factory_file_path.' (you can find a template in src/Ingenerator/RunSingle/'.self::DB_DRIVER_FACTORY_FILE.').');
        }

        $logger = new ConsoleLogger;

        $driver = include($driver_factory_file_path);

        $runner    = new CommandRunner;
        $runsingle = new RunSingle($driver, $runner, $logger);

        return $runsingle;
    }

}
