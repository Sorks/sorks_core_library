<?php

namespace s\facade;

use s\Facade;

class App extends Facade
{
    protected static function getFacadeClass()
    {
        return 'App';
    }
}