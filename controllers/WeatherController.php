<?php

namespace app\controllers;

use yii\rest\Controller;

use \Firebase\JWT\JWT;
class WeatherController extends Controller
{
    public function actionGetWeather()
    {
        include_once '../config/core.php';
        include_once '../libs/php-jwt-master/src/BeforeValidException.php';
        include_once '../libs/php-jwt-master/src/ExpiredException.php';
        include_once '../libs/php-jwt-master/src/SignatureInvalidException.php';
        include_once '../libs/php-jwt-master/src/JWT.php';
        $data = json_decode(file_get_contents("php://input"));
        $lat=$data ->lat;
        $lon=$data ->lon;
        $jwt=isset($data->jwt) ? $data->jwt : "";

        if($jwt)
        {
            try
            {
                $decoded = JWT::decode($jwt, $key, array('HS256'));

                // код ответа 
                http_response_code(200);
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
                echo ($responce);
            }
            catch (Exception $e){

                // код ответа 
                http_response_code(401);
            
                // сообщить пользователю отказано в доступе и показать сообщение об ошибке 
                echo json_encode(array(
                    "message" => "Доступ закрыт.",
                    "error" => $e->getMessage()
                ));
            }
        }
        else
        {
            http_response_code(401);

            // сообщить пользователю что доступ запрещен 
            echo json_encode(array("message" => "Доступ запрещён."));
        }
    }
}