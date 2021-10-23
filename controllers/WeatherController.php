<?php

namespace app\controllers;

use yii\rest\ActiveController;
use yii\web\Controller;

class WeatherController extends Controller
{
    public function actionGetWeather($lat,$lon)
    {
        $url = 'http://api.openweathermap.org/data/2.5/weather';

        $options = array(
            'lat'=> $lat,
            'lon'=> $lon,
            'appid'=>'ed91cf347bfe28a62e07c4b5a5fb48ab',
            'units'=> 'metric',
            'lang'=> 'ru',
        );

        $ch=curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($options));

        $responce=curl_exec($ch);

        curl_close($ch);

        return $this->render('get-weather', ['response' => $responce]);
    }
}