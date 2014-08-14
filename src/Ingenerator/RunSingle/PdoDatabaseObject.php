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
     * @var bool
     */
    protected $is_initialised = FALSE;

    /**
     * @param \PDO      $pdo
     * @param string    $db_table_name
     */
    public function __construct(\PDO $pdo, $db_table_name)
    {
        $this->pdo           = $pdo;
        $this->db_table_name = $db_table_name;
    }

    protected function init()
    {
        if (! $this->is_initialised) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->is_initialised = TRUE;
        }
    }

    /**
     * @return string
     */
    public function get_db_table_name()
    {
        return $this->db_table_name;
    }

    /**
     * @param string  $sql
     * @param mixed[] $params
     *
     * @return \PDOStatement
     */
    public function execute($sql, $params)
    {
        $this->init();
        $q = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $q->bindParam($key, $value);
        }

        $q->execute($params);

        return $q;
    }

    /**
     * @param string  $sql
     * @param mixed[] $params
     *
     * @return string[]|object|false
     */
    public function fetch_all($sql, $params)
    {
        $this->init();
        $q = $this->execute($sql, $params);

        return $q->fetchAll();
    }

}
