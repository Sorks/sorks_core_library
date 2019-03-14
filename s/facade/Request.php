<?php

namespace s\facade;

use s\Facade;

class Request extends Facade
{
    protected static function getFacadeClass()
    {
        return 'Request';
    }
}