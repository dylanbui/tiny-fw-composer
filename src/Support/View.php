<?php

/**
 * Created by PhpStorm.
 * User: dylanbui
 * Date: 9/12/16
 * Time: 11:22 PM
 */

namespace TinyFw\Support;

class View extends SupportInterface
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */

    protected static function getSupportClass()
    {
        return "oView";
    }

}