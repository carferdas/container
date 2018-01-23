<?php

namespace carlosjfernandes\Container;

use Exception;

abstract class Facade
{
    protected static $container;

    public static function setContainer(Container $container)
    {
        static::$container = $container;
    }

    public static function getContainer()
    {
        return static::$container;
    }

    public static function getAccessor()
    {
        return new Exception('Please define the getAccessor method in your facade');
    }

    public static function getInstance()
    {
        return static::getContainer()->make(static::getAccessor());
    }

    public static function __callStatic($method, $args)
    {
        $object = static::getInstance();

        return call_user_func_array([$object, $method], $args);
    }

}