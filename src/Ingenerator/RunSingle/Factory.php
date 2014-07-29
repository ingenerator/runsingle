<?php
/**
 * Factory to create RunSingle object
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   proprietary
 */


namespace Ingenerator\RunSingle;

use \Ingenerator\RunSingle\PdoDatabaseObject;
use \Ingenerator\RunSingle\DbDriver;
use \Ingenerator\RunSingle\CommandRunner;

class Factory {

    const DB_DRIVER_FACTORY_FILE = 'run_single_config.php';

    public static function create()
    {
        if(!file_exists(self::DB_DRIVER_FACTORY_FILE)){
            throw(new \Exception('Please copy the config file named ' . self::DB_DRIVER_FACTORY_FILE . ' from the RunSingle project directory to your current working directory ' . realpath('./') . ' and change the database settings.' ));
        }
        $driver = include(realpath('./') . '/' . self::DB_DRIVER_FACTORY_FILE);

        $runner = new CommandRunner;
        $runsingle = new RunSingle($driver, $runner);
        return $runsingle;
    }

}
