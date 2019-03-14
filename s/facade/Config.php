<?php

namespace s\facade;

use s\Facade;

class Config extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Config';
    }
}