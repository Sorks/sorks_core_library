<?php

namespace s\facade;

use s\Facade;

class Db extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Db';
    }
}