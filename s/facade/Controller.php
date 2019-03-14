<?php

namespace s\facade;

use s\Facade;

class Controller extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Controller';
    }
}