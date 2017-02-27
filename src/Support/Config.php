<?php

/**
 * Created by PhpStorm.
 * User: dylanbui
 * Date: 9/12/16
 * Time: 11:22 PM
 */

namespace TinyFw\Support;

class Config extends SupportInterface
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */

    protected static function getSupportClass()
    {
        return "oConfig";
    }

}