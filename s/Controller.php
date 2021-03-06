<?php

namespace s;

use ReflectionClass;
use s\facade\Request;
use s\facade\Env;

class Controller
{
    private $controllers = [];

    public function init()
    {
        $module = Request::module();
        $ctrl = Request::controller();
        $action = Request::action();
        $ctrlFile = Env::get('app').'/'.$module.'/controller/'.$ctrl.'.php';

        $class = '\\app\\'.$module.'\\controller\\'.$ctrl;

        if (!in_array($class, $this->controllers)) {
            if (!is_file($ctrlFile)) {
                throw new \Exception('controller not exists：'.$ctrlFile);
                return ;
            }
    
            if (!class_exists($class)) {
                throw new \Exception('class not exists：'.$class);
                return ;
            }
    
            $obj = new ReflectionClass($class);
            if (!$obj->hasMethod($action)) {
                throw new \Exception('method not exists：'.$action);
                return ;
            }

            $controller = new $class;
            $this->controllers[$class] = $controller;
        }
        return call_user_func([$this->controllers[$class], $action]);
    }
}