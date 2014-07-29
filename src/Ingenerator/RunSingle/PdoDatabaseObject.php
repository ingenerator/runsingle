<?php
/**
* Class encapsulating the DB connection.
*
* @author    Matthias Gisder <matthias@ingenerator.com>
* @copyright 2014 inGenerator Ltd
* @licence   proprietary
*/

namespace Ingenerator\RunSingle;

use \PDO;

class PdoDatabaseObject
{
    protected $db_name;
    protected $db_table_name;

    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct($pdo, $db_name, $db_table_name)
    {
        $this->pdo = $pdo;
        $this->db_name = $this->get_db_name($db_name);
        $this->db_table_name =$db_table_name;
    }

    public function init()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_name = $this->get_db_name($this->db_name);
        $this->pdo->query("use $db_name");
    }

    public function get_db_name($db_name)
    {
        return $db_name;
    }

    public function get_db_table_name()
    {
        return $this->db_table_name;
    }

    public function execute($sql, $params)
    {
        $q = $this->pdo->prepare($sql);

        foreach($params as $key => $value) {
            $q->bindParam($key, $value);
        }

        $q->execute($params);
    }

    public function fetch_all($sql, $params)
    {
        $q = $this->pdo->prepare($sql);

        foreach($params as $key => $value) {
            $q->bindParam($key, $value);
        }

        $q->execute($params);

        $result = $q->fetchAll();
        return $result;
    }

}
