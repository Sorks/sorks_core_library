<?php

namespace s;

class Config
{
    public function get($file, $key = '')
    {
        $module = Request::module();
        $moduleCanfPath = Env::get('app').'/'.$module.'/config/'.$file.'.php';
        if ( is_file($moduleCanfPath) ) {
            $read = include $moduleCanfPath;
            if ($key === '') {
                return $read;
            } else {
                if (isset($read[$key])) {
                    return $read[$key];
                }
            }
        }

        $filepath = Env::get('root').'config/'.$file.'.php';
        if ( is_file($filepath) ) {
            $read = include $filepath;
            if ($key === '') {
                return $read;
            } else {
                if (isset($read[$key])) {
                    return $read[$key];
                } else {
                    throw new \Exception('配置不存在：'.$file.'["'.$key.'"]');
                }
            }
        }

        throw new \Exception('配置不存在：'.$file);
    }
}