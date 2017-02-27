<?php

/**
 * Created by PhpStorm.
 * User: dylanbui
 * Date: 9/11/16
 * Time: 11:18 AM
 */

namespace TinyFw\Support;

use TinyFw\Core\Container;

abstract class SupportInterface
{
    static protected $instanceRegister = null;
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getSupportClass()
    {
        throw new \RuntimeException("Support child class does not implement getSupportClass method.");
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
//        if (is_null(self::$instanceRegister))
//        {
//            $instance = \TinyFw\Core\FrontController::getInstance();
//            self::$instanceRegister = $instance->getRegistry();
//        }
//
//        $class = static::getSupportClass();
//        $instance = self::$instanceRegister->{$class};

        $class = static::getSupportClass();
//        $instance = Registry::getInstance()->{$class};// Application::$registerInstance->{$class};
        $instance = Container::$_container[$class];
        switch (count($args))
        {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }


}