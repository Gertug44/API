<?php

namespace app\controllers;

use app\controllers\ApiController;
use app\components\JWTCom;
use yii;
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
                    $this->responce(200,array(
                      "message" => json_decode($responce)
                    ));
                }
                else{
                    // сообщить пользователю отказано в доступе и показать сообщение об ошибке 
                    $this->responce(400,array(
                        "message" => "Доступ закрыт.",
                        "error" => $result['error']
                    ));
                }
            }
        else
        {
            // сообщить пользователю что доступ запрещен 
            $this->responce(401,array("message" => "Доступ запрещён."));
        }
    }
}