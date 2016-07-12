<?php

namespace TinyFw\Helper;

class ExArray 
{
    public function __construct()
    {
    }

    public static function recursive($sourceArr,$parent = 0,$level = 1,&$resultArr){
        if(count($sourceArr)>0){
            foreach ($sourceArr as $key => $value){
                if($value['parent'] == $parent){
                    $value['level'] = $level;
                    $resultArr[] = $value;
                    $newParents = $value['id'];
                    unset($sourceArr[$key]);
                    recursive($sourceArr,$newParents, $level + 1,$resultArr);
                }
            }
        }
    }

    public static function htmlSelectFromRecursiveArray($name,$value = null, $options, $attribs = null, $size = null ){
        if($size >1){
            $strSize = 'size="'. $size .'"';
        }
        $xhtml = '<select name="' . $name . '" id="' . $name . '" style="' . $attribs . '" ' . $strSize .' >';

        foreach ($options as $key=> $info){
            $strSelect = '';
            if($info['id'] == $value){
                $strSelect = 'selected="selected"';
            }

            if($info['level'] == 1){
                $xhtml .= '<option value="' . $info['id'] . '" ' . $strSelect . '>+' . $info['name'] . '</option>';
            }else{
                $string = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                $newString = '';
                for($i=1;$i<$info['level']; $i++){
                    $newString .= $string;
                }
                $info['name'] = $newString . '-' . $info['name'];
                $xhtml .= '<option value="' . $info['id'] . '" ' . $strSelect . '>' . $info['name'] . '</option>';
            }
        }

        $xhtml .= '</select>';

        return $xhtml;
    }

    /**
     * Merges any number of arrays / parameters recursively, replacing
     * entries with string keys with values from latter arrays.
     * If the entry or the next value to be assigned is an array, then it
     * automagically treats both arguments as an array.
     * Numeric entries are appended, not replaced, but only if they are
     * unique
     *
     * calling: result = array_merge_recursive_distinct(a1, a2, ... aN)
     **/

    public static function arrayMergeRecursiveDistinct()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);
        if(!is_array($base)) $base = empty($base) ? array() : array($base);
        foreach($arrays as $append)
        {
            if(!is_array($append)) $append = array($append);
            foreach($append as $key => $value) {
                if(!array_key_exists($key, $base) and !is_numeric($key))
                {
                    $base[$key] = $append[$key];
                    continue;
                }
                if(is_array($value) or is_array($base[$key]))
                {
                    $base[$key] = self::arrayMergeRecursiveDistinct($base[$key], $append[$key]);
                } else if(is_numeric($key))
                {
                    if(!in_array($value, $base)) $base[] = $value;
                } else
                {
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }

}