<?php

namespace s;

class Route
{
    public $module;
    public $ctrl;
    public $action;

    public function __construct() {
        $this->parse();
    }

    private function parse()
    {
        $path = Env::get('route');
        if (is_dir($path)) {
            $files = $this->scanFile($path) ?: [];
            $filesNum = count($files);
            list($i, $routes) = [0, []];
            while ($i < $filesNum) {
                if ( is_file($files[$i]) ) {
                    $includeRes = include $files[$i];
                    if (is_array($includeRes)) {
                        $routes += $includeRes;
                    }
                }
                $i++;
            }
    
            $requrl = trim(substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')), '/') ?: 'index';
            if (isset($requrl) && isset($routes[$requrl])) {
                $route = explode('/', $routes[$requrl]);
                if (count($route) < 3) {
                    throw new \Exception('路由表达式错误：'.$routes[$requrl]);
                }
                $this->module = $route[0];
                $this->ctrl = $route[1];
                $this->action = $route[2];
            }
        } else {
            throw new \Exception('路由文件丢失');
        }
    }

    private function scanFile($path)
    {
        global $res;
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    $this->scanFile($path . '/' . $file);
                } else {
                    $res[] = $path.'/'.$file;
                }
            }
        }
        return $res;
    }

    public function module()
    {
        return $this->module;
    }

    public function controller()
    {
        return $this->ctrl;
    }

    public function action()
    {
        return $this->action;
    }
}