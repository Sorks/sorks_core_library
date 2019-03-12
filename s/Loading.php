<?php

namespace s;

class Loading
{
    static $classMap    = [];
    static $namespace   = [
        's'     => ROOT.'vendor/sorks/library/'
    ];

    public static function register()
    {
        spl_autoload_register('s\\Loading::autoload', true, true);
    }

    public static function autoload($class)
    {
        list($path) = explode('\\', $class);
        if (!isset(self::$namespace[$path])) {
            self::$namespace[$path] = ROOT.'/';
        }
        $classFile = str_replace('\\', '/', self::$namespace[$path].$class.'.php');
        if (!isset(self::$classMap[$classFile]) && is_file($classFile)) {
            require $classFile;
            self::$classMap[$classFile] = $classFile;
        }
        return true;
    }
}