<?php

namespace s\facade;

use s\Facade;

class Cache extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Cache';
    }
}