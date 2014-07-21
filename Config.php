<?php
/**
* Configuration
*
* @author    Matthias Gisder <matthias@ingenerator.com>
* @copyright 2014 inGenerator Ltd
* @licence   proprietary
*/

namespace RunSingle;

require_once('MySqlConfigData.php');
require_once('DbDriver.php');

class Config
{
    protected $driver;

    public function init()
    {
        $this->driver =  new DbDriver(new MySqlConfigData);
        $this->driver->init();

// For a file system-only solution
//        $this->driver =  new FileSystemDriver;
    }

    public function get_driver()
    {
        return $this->driver;
    }

}

