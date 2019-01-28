<?php

namespace s;

class Controller
{
    public static function init()
    {
        $module = Request::module();
        $ctrl = Request::controller();
        $action = Request::action();
        $ctrlFile = Env::get('app').'/'.$module.'/controller/'.$ctrl.'.php';
        $class = '\\app\\'.$module.'\\controller\\'.$ctrl;
        if (is_file($ctrlFile)) {
            include $ctrlFile;
            $controller = new $class();
            $controller->$action();
        } else {
            throw new \Exception('控制器不存在：'.$ctrlFile);
        }
    }
}