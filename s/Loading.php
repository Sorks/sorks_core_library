<?php

namespace s;

class Loading
{
    static $classMap = [];
    public static function register()
    {
        spl_autoload_register('s\\Loading::autoload', true, true);
    }

    public static function autoload($class)
    {
        $classFile = ROOT.'/vendor/sorks/library/'.$class.'.php';
        
        if (!isset(self::$classMap[$classFile]) && is_file($classFile)) {
            require $classFile;
            self::$classMap[$classFile] = $classFile;
        }
        return true;
    }
}