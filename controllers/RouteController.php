<?php

namespace app\controllers;

use Exception;
use yii\web\Controller;
use Yii;
use app\components\JWTCom;
use app\components\Logger;

class RouteController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionGetRoute()        //Возвращает маршрут с углами поворотов и погодой
    {
        $request = Yii::$app->request;

        if ($request->get('key')!=null)
        {
            $result = JWTCom::decodeJWT($request->get('key'));
            if ($result["status"] == "success") 
            {
                Logger::getLogger("dev")->log("Начато получение API маршрута");
                if ($request->get('FromLat') != null && $request->get('FromLng') != null && $request->get('ToLat') != null && $request->get('ToLng') != null) 
                {
                    $content = $this->getTurns(false);
                    Logger::getLogger("dev")->log("Успешно получен маршрут по ключу: ".$request->get('key'));
                    $this->responce(array(
                        "status" => "success",
                        "route" => json_encode($content)
                    ), 200);
                }
                Logger::getLogger("dev")->log("Введен не полный запрос");
                $this->responce(array(
                    "status" => "fail",
                    "message" => "Ошибка запроса(недостаточно данных).",
                    "error" => $result['error']
                ), 400);
            }
            Logger::getLogger("dev")->log("Что-то пошло не так");
            $this->responce(array(
                "status" => "fail",
                "message" => "Доступ закрыт.",
                "error" => $result['error']
            ), 400);
        }
        
        Logger::getLogger("dev")->log("Пользователь не авторизован");
        $this->responce(array(
            "status" => "fail",
            "message" => "Доступ запрещён."),401);
    }
    
    private function getTurns($returnfull = true)         //Функция выбирает из общего массива данных только повороты 
    {
        $request = Yii::$app->request;
        $data = $this->getFullApi($request->get('FromLat'),$request->get('FromLng'),$request->get('ToLat'),$request->get('ToLng'));
        $turns = [];
        try 
        {
            foreach ($data['route']['legs']['0']['maneuvers'] as &$startpoint){ 
                $turns[] = &$startpoint['startPoint'];
            }
            Logger::getLogger("dev")->log("Успешно получены все повороты маршрута");
            $this->InsertAngleIntoArr($turns);
            $this->InsertWeatherIntoArr($turns);
            return $returnfull==true? $data :$turns;          //При returnfull==true возвращает изначальный массив, с добавленными данными, при false только повороты с добавленными данными
        }   
        catch (Exception $e) 
        {
            Logger::getLogger("dev")->log("Ошибка добавления".$e->getMessage() );
            $this->responce(array(
                "status" => "fail",
                "message" => "Ошибка добавления".$e->getMessage(),
            ), 400);
        }
    }

    private function InsertWeatherIntoArr(&$array)              //Вставляет в общий массив коэффициенты погоды для каждой точки маршрута
    {

        for ($i = 0; $i <= count($array) - 1; $i++) {
            
         }
        Logger::getLogger("dev")->log("Успешно добавлена погода на все повороты маршрута");
    }
    private function InsertAngleIntoArr(&$array)                //Вставляет в общий массив углы поворотов 
    {
        for ($i = 1; $i <= count($array) - 2; $i++) {
            $array[$i]['angle'] = $this->CalculateAngleByVectors($array[$i - 1], $array[$i], $array[$i + 1]);
        }
        Logger::getLogger("dev")->log("Успешно добавлены углы поворотов маршрута");
    }

    private function CalculateAngleByVectors($prev, $current, $next)        //Вычисляет угол по трем векторам
    {
        $vectorX = [
            $current['lat'] - $prev['lat'], 
            $current['lng'] - $prev['lng']
        ];
        $vectorY = [
            $current['lat'] - $next['lat'], 
            $current['lng'] - $next['lng']
        ];
        $scalarmul = $vectorX[0] * $vectorY[0] + $vectorX[1] * $vectorY[1];
        $LengthX = sqrt(pow($vectorX[0], 2) + pow($vectorX[1], 2));
        $LengthY = sqrt(pow($vectorY[0], 2) + pow($vectorY[1], 2));
        return round(acos(($scalarmul / ($LengthX * $LengthY)))*180/pi(), 2);
    }


    private function getFullApi($FromLat,$FromLng,$ToLat,$ToLng )           //Получение общего массива маршрута по АПИ 
    {
        Logger::getLogger("dev")->log("Получение маршрута с MapQuest");
        $curl = Yii::$app->params['routeURL'].Yii::$app->params['routeKey']."&from=".$FromLat.",".$FromLng."&to=".$ToLat.",".$ToLng;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $curl);
        $responce = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($responce, true);
        return $data;
    }

}
