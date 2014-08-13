<?php
/**
 * Database driver factory
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\RunSingle;

class DbDriverFactory
{
    /**
     * @param array $credentials
     *
     * @return DbDriver
     */
    public static function factory($credentials)
    {
        $pdo       = new \PDO($credentials['dsn'], $credentials['db_user'], $credentials['db_pass']);
        $db_object = new PdoDatabaseObject($pdo, $credentials['db_table_name']);

        return new DbDriver($db_object);
    }

}
