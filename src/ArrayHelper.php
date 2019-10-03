<?php
namespace yii2cmf\helpers;

class ArrayHelper
{

    public static function unset($value, &$array)
    {
        if (($key = array_search($value, $array)) !== false) {
            unset($array[$key]);
        }
    }

}