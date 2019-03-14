<?php

namespace s\facade;

use s\Facade;

class Session extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Session';
    }
}