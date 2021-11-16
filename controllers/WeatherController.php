<?php

namespace app\controllers;

use app\controllers\ApiController;
use app\components\JWTCom;
use yii;
use app\components\Logger;
class WeatherController extends ApiController
{
    public $enableCsrfValidation = false;
    public function actionGet()
    {
        $post=(file_get_contents("php://input"));
        $data = json_decode($post);
        $lat=$data ->lat;
        $lon=$data ->lon;
        $jwt=isset($data->jwt) ? $data->jwt : "";
        Logger::getLogger("dev")->log("Получаем погодку");
        if($jwt)
        {
                $result=JWTCom::decodeJWT($jwt);
                if ($result["status"]=="success")
                {
                    $url = Yii::$app->params['weatherURL'];
                    $options = array(
                       'lat'=> $lat,
                       'lon'=> $lon,
                       'appid'=>Yii::$app->params['weatherKey'],
                       'units'=> 'metric',
                       'lang'=> 'ru',
                    );
    
                    $ch=curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($options));
    
                    $responce=curl_exec($ch);
    
                    curl_close($ch);
                    Logger::getLogger("dev")->log("Все четко лови погоду");
                    $this->responce(array(
                      "status" => "success",
                      "weather" => json_decode($responce)
                    ));
                }

                Logger::getLogger("dev")->log("Что-то пошло не так");
                // сообщить пользователю отказано в доступе и показать сообщение об ошибке 
                $this->responce(array(
                    "status" => "fail",
                    "message" => "Доступ закрыт.",
                    "error" => $result['error']
                ),400);
        }

        Logger::getLogger("dev")->log("Пустой jwt");
        // сообщить пользователю что доступ запрещен 
        $this->responce(array(
            "status" => "fail",
            "message" => "Доступ запрещён."),400);
    }
}