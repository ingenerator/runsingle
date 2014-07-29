<?php

namespace Ingenerator\RunSingle;

class DbSetup {

    protected $pdo;
    protected $db_name;
    protected $db_table_name;

    public function __construct($pdo, $db_name, $db_table_name)
    {
        $this->pdo = $pdo;
        $this->db_name = $db_name;
        $this->db_table_name = $db_table_name;
    }

    public function execute()
    {
        $this->setup_database();
        $this->pdo->query("use " . $this->get_db_name($this->db_name));

        $this->setup_table();
    }

    function setup_database()
    {
        echo "setting up database ...\n";
        $this->pdo->query("CREATE DATABASE IF NOT EXISTS " . $this->get_db_name($this->db_name));
        echo "done!\n";
    }

    function setup_table()
    {
        echo "setting up table ...$this->db_table_name\n";
        $this->pdo->query("CREATE TABLE IF NOT EXISTS " . $this->get_db_table_name() . "(
                          task_name varchar(255),
                          lock_timestamp int,
                          timeout int,
                          PRIMARY KEY (task_name)
                        )");
        echo "done!\n";
    }

    public function get_db_name($db_name)
    {
        $db_name = "`".str_replace("`","``", $db_name)."`";
        return $db_name;
    }

    public function get_db_table_name()
    {
        return $this->db_table_name;
    }
}

$credentials_file_name = 'credentials.php';

if (is_file($autoload = getcwd() . '/vendor/autoload.php')) {
    require $autoload;
}

if(!file_exists($credentials_file_name)){
    throw(new \Exception('Please copy the config file named ' . $credentials_file_name . ' from the RunSingle project directory to your current working directory ' . realpath('./') . ' and change the database settings.' ));
}
include(realpath('./') . '/' . $credentials_file_name);
$pdo = new \PDO($db . ":host=" . $db_host, $db_user, $db_pass);
$db_object = new \Ingenerator\RunSingle\PdoDatabaseObject($pdo, $db_name, $db_table_name);
$driver = new \Ingenerator\RunSingle\DbDriver($db_object);

$dbs = new \Ingenerator\RunSingle\DbSetup($pdo, $db_name, $db_table_name);
$dbs->execute();
