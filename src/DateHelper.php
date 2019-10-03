<?php
namespace yii2cmf\helpers;

use Yii;

class DateHelper
{

    public static function getCurrent($format = 'Y-m-d H:i:s')
    {
        return date($format, time());
    }

    /**
     * @param $datetime
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function formatDatetime($datetime, $format = 'php:Y-m-d H:i')
    {
        return Yii::$app->formatter->asDatetime($datetime, $format);
    }

}