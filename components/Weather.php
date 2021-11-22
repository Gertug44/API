<?php

namespace app\components;

use Exception;
use Yii;

class Weather
{
    public static function getWeather($lat, $lng)             //Возвращает 
    {
        try {
            $url = Yii::$app->params['weatherURL'];
            $options = array(
               'lat'=> $lat,
               'lon'=> $lng,
               'appid'=>Yii::$app->params['weatherKey'],
               'units'=> 'metric',
               'lang'=> 'ru',
            );

            $ch=curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($options));

            $responce=curl_exec($ch);

            curl_close($ch);
            Logger::getLogger("dev")->log("Успешно добавлена погода маршрута");
            return json_decode($responce);
        } catch (Exception $e) {
            Logger::getLogger("dev")->log("Ошибка добавления погоды маршрута" . $e->getMessage());
        }
    }
}
