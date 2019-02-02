<?php

namespace s;

class Request
{
    public static function init()
    {
        $_GET = [];
        $_REQUEST = [];
    }

    public static function ssl()
    {
        if ($_SERVER['REQUEST_SCHEME'] == 'https') {
            return 'https';
        } else {
            return 'http';
        }
    }

    public static function ip()
    {
        list($ip, $ipData) = ['', ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_FROM', 'REMOTE_ADDR']];
        foreach ($ipData as $v) {
            if (isset($_SERVER[$v])) {
                if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $_SERVER[$v])) {
                    continue;
                } 
                $ip = $_SERVER[$v];
            }
        }
        return $ip;
    }

    public static function sessionId()
    {
        return $_COOKIE['PHPSESSID'];
    }

    public static function param($key = '')
    {
        if (empty($_POST)) {
            return [];
        }

        if ($key == '') {
            $param = $_POST;
        } else {
            $param = $_POST[$key];
        }

        if (!is_array($param)) {
            return htmlspecialchars($param);
        }

        array_walk_recursive($param, function(&$val, $key) {
            $val = htmlspecialchars($val);
            return $val;
        });
        
        return $param;
    }

    public static function __callStatic($method, $params) {
        return call_user_func_array([new Route, $method], $params);
    }
}