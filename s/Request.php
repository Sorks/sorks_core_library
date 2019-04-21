<?php

namespace s;

class Request
{
    private static $route = null;
    public function ssl()
    {
        if ($_SERVER['REQUEST_SCHEME'] == 'https') {
            return 'https';
        } else {
            return 'http';
        }
    }

    public function ip()
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

    public function param($key = null, $type = 'param')
    {
        switch ($type) {
            case 'get':
                $request = $_GET;
                break;
            case 'post':
                $request = $_POST;
                break;
            default :
                $request = $_REQUEST;
        }

        if (empty($request)) {
            return [];
        }

        if (is_null($key)) {
            $param = $request;
        } else {
            $param = $request[$key];
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

    public function get($key = null)
    {
        return $this->param($key, 'get');
    }

    public function post($key = null)
    {
        return $this->param($key, 'post');
    }

    public function __call($method, $params) {
        switch ($method) {
            case 'module':
                $class = 's\Route';
                break;
            case 'controller':
                $class = 's\Route';
                break;
            case 'action':
                $class = 's\Route';
                break;
            default:
                throw new \Exception('call to undefined function: '.$method);
                return ;
        }
        return call_user_func_array([new $class, $method], $params);
    }
}