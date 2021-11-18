<?php

namespace app\components;

use Exception;
use Yii;

class Weather
{
    public static function getWeather($lat, $lng, $jwt)             //Возвращает 
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => Yii::$app->params['weatherGetURL'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => '{"lat":' . $lat . ',"lon":"' . $lng . '","jwt":"' . $jwt . '"}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);

            curl_close($curl);
            Logger::getLogger("dev")->log("Успешно добавлена погода маршрута");
            return json_decode($response);
        } catch (Exception $e) {
            Logger::getLogger("dev")->log("Ошибка добавления погоды маршрута" . $e->getMessage());
        }
    }
}
