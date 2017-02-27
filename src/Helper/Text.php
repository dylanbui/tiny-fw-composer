<?php

namespace TinyFw\Helper;

final class Text {

    public static function strToUrl($str = NULL, $sperator = "-")
    {
        if(!$str) return NULL;

        $str = mb_strtolower($str,'utf-8');
        $str = self::textToVN($str);

        $str = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;', '*', '/')," ",$str);
        $str = preg_replace("/[^a-zA-Z0-9- ]/", "-", $str);
        $str = preg_replace('/\s\s+/', ' ', $str );
        $str = trim($str);
        $str = preg_replace('/\s+/', $sperator, $str );

        $str = str_replace("----","-",$str);
        $str = str_replace("---","-",$str);
        $str = str_replace("--","-",$str);
        $str = trim($str, $sperator);
        return strtolower($str);
    }

    public static function textToVN($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);

        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", "A", $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", "E", $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", "I", $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", "O", $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", "U", $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", "Y", $str);
        $str = preg_replace("/(Đ)/", "D", $str);

        return $str;
    }

    public static function wordLimiter($str, $limit = 100, $end_char = '&#8230;')
    {
        if (trim($str) == '')
        {
            return $str;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $str, $matches);

        if (strlen($str) == strlen($matches[0]))
        {
            $end_char = '';
        }

        return rtrim($matches[0]).$end_char;
    }

    //// underscored to upper-camelcase
    //// e.g. "this_method_name" -> "ThisMethodName"
    public static function upperCamelcase($string)
    {
        // -- User for php 5.6 -> 7 --
        return preg_replace_callback(
            '/(?:^|-)(.?)/',
            function($match) { return strtoupper($match[1]); },
            $string
        );
    }

    //// underscored to lower-camelcase
    //// e.g. "this_method_name" -> "thisMethodName"
    public static function lowerCamelcase($string)
    {
        // -- User for php 5.6 -> 7 --
        return preg_replace_callback(
            '/-(.?)/',
            function($match) { return strtoupper($match[1]); },
            $string
        );
    }

    // camelcase (lower or upper) to hyphen
    // e.g. "thisMethodName" -> "this_method_name"
    // e.g. "ThisMethodName" -> "this_method_name"
    // Of course these aren't 100% symmetric.  For example...
    //  * this_is_a_string -> ThisIsAString -> this_is_astring
    //  * GetURLForString -> get_urlfor_string -> GetUrlforString
    public static function camelcaseToHyphen($string)
    {
        // -- User for php 5.6 -> 7 --
        return preg_replace_callback(
            '/([^A-Z])([A-Z])/',
            function($match) { return $match[1].'-'.$match[2]; },
            $string
        );

    }

}


