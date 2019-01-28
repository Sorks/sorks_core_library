<?php

namespace s;

use Closure;
use ReflectionClass;
use ReflectionException;

class Container
{
    protected static $instance;
    protected $instances = [];

    protected $bind = [
        'db'            =>  Db::class,
        'app'           =>  App::class,
        'env'           =>  Env::class,
        'route'         =>  Route::class,
        'config'        =>  Config::class,
        'request'       =>  Request::class,
        'controller'    =>  Controller::class,
    ];

    public static function get()
    {
        return static::getInstance()->make('app');
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    public function make($abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bind[$abstract])) {
            $object = $this->invokeClass($this->bind[$abstract]);
        } else {
            $object = $this->invokeClass($abstract);
        }

        $this->instances[$abstract] = $object;

        return $object;
    }

    public function invokeClass($class)
    {
        try {
            $reflect = new ReflectionClass($class);
            $constructor = $reflect->getConstructor();
            $args = $constructor ? $this->bindParams($constructor) : [];
            
            return $reflect->newInstanceArgs($args);
        } catch (ReflectionException $e) {
            throw new \Exception('class not exists: ' . $class);
        }
    }
}