<?php

namespace s;

class Env
{
    public function get($key)
    {
        $root = ROOT;
        switch ($key) {
            case 'root':
                return $root;
            break;
            case 'app':
                return $root.'app';
            break;
            case 'lib':
                return $root.'vendor/sorks/library';
            break;
            case 'static':
                return $root.'static';
            break;
            case 'route':
                return $root.'route';
            break;
            case 'config':
                return $root.'config';
            break;
            default:
                return false;
        }
    }
}