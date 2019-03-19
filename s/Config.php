<?php

namespace s;

use s\facade\Env;
use s\facade\Request;

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

        $filepath = Env::get('config').'/'.$file.'.php';
        if ( is_file($filepath) ) {
            $read = include $filepath;
            if ($key === '') {
                return $read;
            } else {
                if (isset($read[$key])) {
                    return $read[$key];
                } else {
                    return null;
                }
            }
        }
        return null;
    }
}