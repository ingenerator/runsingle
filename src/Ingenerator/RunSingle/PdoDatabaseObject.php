<?php
/**
 * Class encapsulating the DB connection.
 *
 * @author    Matthias Gisder <matthias@ingenerator.com>
 * @copyright 2014 inGenerator Ltd
 * @licence   BSD
 */

namespace Ingenerator\RunSingle;

use \PDO;

class PdoDatabaseObject
{
    /**
     * @var string
     */
    protected $db_table_name;

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @param \PDO $pdo
     * @param      $db_table_name
     */
    public function __construct(\PDO $pdo, $db_table_name)
    {
        $this->pdo           = $pdo;
        $this->db_table_name = $db_table_name;
    }

    public function init()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @return string
     */
    public function get_db_table_name()
    {
        return $this->db_table_name;
    }

    /**
     * @param $sql
     * @param $params
     *
     * @return \PDOStatement
     */
    public function execute($sql, $params)
    {
        $q = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $q->bindParam($key, $value);
        }

        $q->execute($params);

        return $q;
    }

    /**
     * @param $sql
     * @param $params
     *
     * @return array
     */
    public function fetch_all($sql, $params)
    {
        $q = $this->execute($sql, $params);

        return $q->fetchAll();
    }

}
