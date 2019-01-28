<?php

namespace s;

use PDO;
use s\db\Query;

class Db
{
    protected static $connection;
    
    protected static $options      = [
        PDO::ATTR_CASE              =>  PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           =>  PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      =>  PDO::NULL_TO_STRING,
        PDO::ATTR_PERSISTENT        =>  true
    ];

    public function __construct() {}

    public static function connect()
    {
        $config = Config::get('database');
        $dsn = 'mysql:host='.$config['hostname'];
        if (!empty($config['database'])) {
            $dsn .= ';dbname='.$db;
        }
        try {
            self::$connection = new PDO($dsn, $config['username'], $config['password'], self::$options);
        } catch (\PDOException $e) {
            var_export($e->getMessage());
        }

        return new Query(self::$connection);
    }

    public static function __callStatic($method, $params) {
        return call_user_func_array([static::connect(), $method], $params);
    }
}