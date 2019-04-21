<?php

namespace s;

class Loading
{
    /**
     * 加载的类文件
     * @var array
     */
    static $classMap    = [];

    /**
     * Composer路径
     * @var string
     */
    static $composerPath = '';

    /**
     * PSR-4
     * @var array
     */
    private static $prefixLengthsPsr4 = [];
    private static $prefixDirsPsr4    = [];
    private static $fallbackDirsPsr4  = [];

    private static $files = [];

    public static function register()
    {
        spl_autoload_register('s\\Loading::autoload', true, true);

        self::$composerPath = ROOT.'vendor/composer/';

        if (is_dir(self::$composerPath)) {
            if (is_file(self::$composerPath.'autoload_static.php')) {
                require self::$composerPath.'autoload_static.php';

                $declaredClass = get_declared_classes();
                $composerClass = end($declaredClass);
                foreach (['prefixLengthsPsr4', 'prefixDirsPsr4', 'fallbackDirsPsr4', 'prefixesPsr0', 'fallbackDirsPsr0', 'classMap', 'files'] as $v) {
                    if (property_exists($composerClass, $v)) {
                        self::${$v} = $composerClass::${$v};
                    }
                }
            }
        }

        self::addNamespace(['s' => __DIR__]);
    }

    public static function autoload($class)
    {
        $file = self::findFile($class);
        if ($file && !isset(self::$classMap[$file])) {
            include $file;
            self::$classMap[$file] = true;
        }
    }

    public static function findFile($class)
    {
        $path = strtr($class, '\\', DS).'.php';
        if (isset(self::$prefixLengthsPsr4[$class[0]])) {
            foreach (self::$prefixLengthsPsr4[$class[0]] as $k => $v) {
                if (0 === strpos($class, $k)) {
                    foreach (self::$prefixDirsPsr4[$k] as $dir) {
                        if (is_file($file = $dir . DS . substr($path, $v))) {
                            return $file;
                        }
                    }
                }
            }
        } elseif (is_file($file = ROOT.$path)) {
            return $file;
        }
        return false;
    }

    public static function addNamespace($namespace)
    {
        if (is_array($namespace)) {
            foreach ($namespace as $k => $v) {
                self::addPsr4($k.'\\', rtrim($v, DS));
            }
        }
    }

    public static function addPsr4($namespace, $path)
    {
        if (!isset(self::$prefixDirsPsr4[$namespace])) {
            $length = strlen($namespace);
            if ('\\' !== $namespace[$length - 1]) {
                throw new \InvalidArgumentException("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            
            self::$prefixDirsPsr4[$namespace] = (array)$path;
            self::$prefixLengthsPsr4[$namespace[0]][$namespace] = $length;
        }
    }
}