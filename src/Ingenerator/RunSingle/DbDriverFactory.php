<?php
/**
 * Database driver factory
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */


namespace Ingenerator\RunSingle;

use \Ingenerator\RunSingle\PdoDatabaseObject;
use \Ingenerator\RunSingle\DbDriver;

class DbDriverFactory
{
    /**
     * @param array $credentials
     *
     * @return DbDriver
     */
    public static function factory($credentials)
    {
        $pdo       = new \PDO($credentials['db'] . ':host=' . $credentials['db_host'], $credentials['db_user'], $credentials['db_pass']);
        $db_object = new PdoDatabaseObject($pdo, $credentials['db_name'], $credentials['db_table_name']);

        return new DbDriver($db_object);
    }

}
