<?php

namespace s;

class App
{
    public static function run()
    {
        $appPath = Env::get('app');

        // 加载应用公共文件
        $appCommonFile = $appPath.'/Common.php';
        if (is_file($appCommonFile)) {
            include_once $appCommonFile;
        }

        // 加载框架函数
        $frameCommonFile = Env::get('lib').'/Common.php';
        if (is_file($frameCommonFile)) {
            include_once $frameCommonFile;
        }
        
        // 加载模块公共文件
        $module = Request::module();
        $moduleCommonFile = $appPath.'/'.$module.'/Common.php';
        if (is_file($moduleCommonFile)) {
            include_once $moduleCommonFile;
        }

        // 加载请求类
        Request::init();
        
        // 加载控制器
        Controller::init();
    }
}