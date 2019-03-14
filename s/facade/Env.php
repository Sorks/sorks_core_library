<?php

namespace s\facade;

use s\Facade;

class Env extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Env';
    }
}