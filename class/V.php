<?php


class V
{
    static $store = [];

    static function get ($name, $default="")
    {
        return V::$store[$name] ?? $default;
    }

    static function set ($name, $value)
    {
        V::$store[$name] = $value;
    }

}