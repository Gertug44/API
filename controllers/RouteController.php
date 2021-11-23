<?php

namespace app\controllers;

use Exception;
use yii\web\Controller;
use Yii;
use app\components\JWTCom;
use app\components\Logger;
use app\components\Weather;

class RouteController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionGetRoute()        //Возвращает маршрут с углами поворотов и погодой
    {
        $request = Yii::$app->request;

        if ($request->get('key') != null) {
            $result = JWTCom::decodeJWT($request->get('key'));
            if ($result["status"] == "success") {
                Logger::getLogger("dev")->log("Начато получение API маршрута");
                if (($request->get('from') != null && $request->get('to')!=null)) {
                    $data = $this->getFullApi($request->get('from'),$request->get('to'));
                    $turns = $this->getTurns($data);
                    $this->InsertAngleIntoArr($turns);
                    $this->InsertWeatherIntoArr($turns);

                    if ($request->get('onlyTurns') == 'true') $data = $turns;
                    Logger::getLogger("dev")->log("Успешно получен маршрут по ключу: " . $request->get('key'));

                    $this->responce(array(
                        "status" => "success",
                        "route" => ($data)
                    ), 200);
                }
                Logger::getLogger("dev")->log("Введен не полный запрос");
                $this->responce(array(
                    "status" => "fail",
                    "message" => "Ошибка запроса(недостаточно данных).",
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
            "message" => "Доступ запрещён."
        ), 401);
    }

    private function getTurns(&$data)         //Функция выбирает из общего массива данных только повороты 
    {
        $turns = [];
        try {
            foreach ($data['route']['legs']['0']['maneuvers'] as &$startpoint) {
                $turns[] = &$startpoint['startPoint'];
            }
            Logger::getLogger("dev")->log("Успешно получены все повороты маршрута");
            return $turns;
        } catch (Exception $e) {
            Logger::getLogger("dev")->log("Ошибка поиска маршрута" . $e->getMessage());
            $this->responce(array(
                "status" => "fail",
                "message" => "Маршрут не найден" . $e->getMessage(),
            ), 400);
        }
        return null;
    }

    private function InsertWeatherIntoArr(&$array)              //Вычисляет погоду для каждого поворота маршрута
    {
        for ($i = 0; $i <= count($array) - 1; $i++) {
            $array[$i]['weather'] = Weather::getWeather($array[$i]['lat'], $array[$i]['lng']);
        }
    }
    private function InsertAngleIntoArr(&$array)                //Вычисляет углы поворотов 
    {

        for ($i = 1; $i <= count($array) - 2; $i++) {
            $array[$i]['angle'] = $this->CalculateAngleByVectors($array[$i - 1], $array[$i], $array[$i + 1]);
        }
    }

    private function CalculateAngleByVectors($prev, $current, $next)        //Вычисляет угол по трем векторам
    {
        try {
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
            return round(acos(($scalarmul / ($LengthX * $LengthY))) * 180 / pi(), 2);
        } catch (Exception $e) {
            Logger::getLogger("dev")->log("Ошибка вычисления угла поворота $e->getMessage()\n" . json_encode($prev) . "\n" . json_encode($current) . "\n" . json_encode($next));
        }
        return "0";
    }


    private function getFullApi($from ,$to)           //Получение общего массива маршрута по АПИ 
    {
        Logger::getLogger("dev")->log("Получение маршрута с MapQuest");

        $curl = curl_init();
        curl_setopt_array($curl,[
            CURLOPT_URL => Yii::$app->params['routeURL']."?key=".Yii::$app->params['routeKey']."&from=$from&to=$to",
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $responce = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($responce,true);
        return $data;
    


    }
}
