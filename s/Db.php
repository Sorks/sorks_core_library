<?php

namespace s;

use PDO;
use s\db\Query;

class Db
{
    protected $connection;
    
    protected $options      = [
        PDO::ATTR_CASE              =>  PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           =>  PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      =>  PDO::NULL_TO_STRING,
        PDO::ATTR_PERSISTENT        =>  true
    ];

    public function __construct() {}

    public function connect()
    {
        $config = Config::get('database');
        $dsn = 'mysql:host='.$config['hostname'];
        if (!empty($config['database'])) {
            $dsn .= ';dbname='.$db;
        }
        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $this->options);
        } catch (\PDOException $e) {
            var_export($e->getMessage());
        }

        return new Query($this->connection);
    }

    public function __call($method, $params) {
        return call_user_func_array([$this->connect(), $method], $params);
    }
}