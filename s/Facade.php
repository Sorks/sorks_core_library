<?php

namespace s;

class Facade
{
    protected static function getFacadeClass() {}

    protected static function bridge()
    {
        $class = 's\\'.static::getFacadeClass();
        return new $class;
    }

    public static function __callStatic($method, $params) {
        return call_user_func_array([static::bridge(), $method], $params);
    }
}