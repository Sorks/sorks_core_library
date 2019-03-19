<?php

namespace s;

use s\facade\Config;
use Redis;

class Cache
{
    protected $config = [];

    protected $defaultConfig = [
        'file' => [
            'expire_time'   => 1800,
            'path'          => 'runtime/cache', 
        ],
        'redis' => [
            'host'      => '127.0.0.1',
            'port'      => 6379,
            'username'  => 'root',
            'password'  => 'root',
        ]
    ];

    public function __construct() {
        $this->config = Config::get('cache') ?: [];
    }

    public function dirver($type = 'file', $cofig = []) {
        if (!empty($config)) {
            $this->config = $config;
        } else if (isset($this->defaultConfig[$type])) {
            $this->config = $this->defaultConfig[$type];
        }

        $this->config['type'] = $type;
        return $this;
    }

    public function __call($method, $params) {
        $type = isset($this->config['type']) ? $this->config['type'] : '';
        switch ($type) {
            case 'file':
                $driver = 's\\cache\\File';
                break;
            case 'redis':
                $driver = 's\\cache\\Redis';
                break;
            default:
                throw new \Exception('驱动不存在：'.$type);
        }
        return call_user_func_array([new $driver($this->config), $method], $params);
    }
}