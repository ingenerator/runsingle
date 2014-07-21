<?php
/**
* Config data object for the DB connection.
*
* @author    Matthias Gisder <matthias@ingenerator.com>
* @copyright 2014 inGenerator Ltd
* @licence   proprietary
*/

namespace RunSingle;

require_once('PdoConfigData.php');

class MySqlConfigData implements PdoConfigData
{
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_NAME = 'run_single';
    const DB_HOST = 'localhost';

    /**
     * @return \PDO
     */
    public function get_pdo()
    {
        return new \PDO("mysql:host=" . self::DB_HOST, self::DB_USER, self::DB_PASS);
    }

    public function get_db_name()
    {
        return self::DB_NAME;
    }
}
