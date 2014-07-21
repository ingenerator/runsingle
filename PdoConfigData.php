<?php
/**
* Config data object for the DB connection.
*
* @author    Matthias Gisder <matthias@ingenerator.com>
* @copyright 2014 inGenerator Ltd
* @licence   proprietary
*/

namespace RunSingle;

interface PdoConfigData
{
    /**
     * @return \PDO
     */
    public function get_pdo();

    public function get_db_name();

}
