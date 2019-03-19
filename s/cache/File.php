<?php

namespace s\cache;

use s\facade\Config;
use s\facade\Env;

class File
{
    protected $config = [];

    protected $cachePath = '';

    public function __construct($config = null) {
        $this->config = $config;
        $this->init();
    }

    private function init()
    {
        if ($this->config['path'] != '') {
            $path = $this->config['path'];
        } else {
            $path = 'runtime/cache';
        }
        $cachePath = Env::get('root').$path;
        $this->cachePath = $cachePath;
        !is_dir($cachePath) && mkdir($cachePath, 0755, true);
    }

    private function getFile($key)
    {
        $tag = $this->getTag($key);
        $filePath = $this->cachePath.'/'.$tag.'.php';
        return $filePath;
    }

    private function getTag($key)
    {
        return md5($key);
    }

    public function set($key = '', $val, $exp = null)
    {
        $file = $this->getFile($key);
        if (is_null($exp)) {
            $exp = $this->config['expire_time'] ?: 1800;
        }
        $data = [serialize($val), time() + $exp];
        $res = file_put_contents($file, $data);
        if ($res) {
            clearstatcache();
            return true;
        } else {
            return false;
        }
    }

    public function get($key = '')
    {
        $file = $this->getFile($key);
        if (!is_file($file)) {
            return false;
        }

        $res = file_get_contents($file);
        if ($res) {
            $timestamp = (int)substr($res, strrpos($res, '}')+1);
            if (time() > $timestamp) {
                $this->unlink($file);
                return false;
            }
            $data = unserialize($res);
            return $data;
        } else {
            return false;
        }
    }

    public function clear($key)
    {
        $file = $this->getFile($key);
        $this->unlink($file);
        if (!is_file($file)) {
            return true;
        } else {
            return false;
        }
    }

    private function unlink($file)
    {
        is_file($file) && unlink($file);
    }
}