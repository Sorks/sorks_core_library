<?php

namespace s;

use s\facade\Config;

class Session
{
    /**
     * session配置
     */
    protected $config   = [];

    /**
     * 是否已经启动
     */
    protected $init     = null;

    public function __construct() {
        $this->config = Config::get('session');
    }

    public function init()
    {
        $config = $this->config;
        $isStart = false;
        
        if (!empty($config['auto_start']) && session_status() != PHP_SESSION_ACTIVE) {
            // 开始跨页传递session 使session不依赖于cookie
            ini_set('session.use_trans_sid', 1);

            // 自动启动session
            ini_set('session.auto_start', 0);
            $isStart = true;
        }

        // SESSION过期时间
        if (isset($config['expire_time'])) {
            ini_set('session.gc_maxlifetime', $config['expire_time']);
            ini_set('session.cookie_lifetime', $config['expire_time']);
        }

        if ($isStart) {
            session_start();
        } else {
            $this->init = false;
        }
        return $this;
    }

    /**
     * 启动session
     */
    public function boot()
    {
        if (is_null($this->init)) {
            $this->init();
        }

        if ($this->init === false) {
            if (session_status() != PHP_SESSION_ACTIVE) {
                session_start();
            }
            $this->init = true;
        }
    }

    public function set($key, $val)
    {
        if (empty($this->init)) {
            $this->boot();
        }

        if (strpos($key, '.')) {
            list($key1, $key2) = explode('.', $key);
            $_SESSION[$key1][$key2] = $val;
        } else {
            $_SESSION[$key] = $val;
        }
    }

    public function get($key = '')
    {
        if (empty($this->init)) {
            $this->boot();
        }

        if (strpos($key, '.')) {
            list($key1, $key2) = explode('.', $key);
            $val = isset($_SESSION[$key1][$key2]) ? $_SESSION[$key1][$key2] : null;
        } else {
            $val = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }
        return $val;
    }
}