<?php

namespace app\controllers;

use Exception;
use yii\web\Controller;
use Yii;

class RouteController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionGetRoute()
    {
        $content = $this->getTurns();
        $request = Yii::$app->request;
        if
        (
            $request->get('FromLat')!=null && $request->get('FromLng')!=null && $request->get('ToLat')!=null && $request->get('ToLng')!=null
        )       
        return json_encode($content);
        else  return json_encode(["Error,  " => "Request error"]);
    }
   
    private function getTurns()
    {
        $request = Yii::$app->request;
        $data = $this->getFullApi($request->get('FromLat'),$request->get('FromLng'),$request->get('ToLat'),$request->get('ToLng'));
        $turns = array();
        try 
        {
            foreach ($data['route']['legs']['0']['maneuvers'] as &$startpoint){ 
                $turns[] = &$startpoint['startPoint'];
            }
            $this->InsertIntoArr($turns);
            return $turns;
        } 
        catch (Exception $e) 
        {
            return ["Error,  " => "Route not found"];
        }
    }

    private function InsertIntoArr(&$array)
    {
        for ($i = 1; $i <= count($array) - 2; $i++) {
            $array[$i]['angle'] = $this->angle($array[$i - 1], $array[$i], $array[$i + 1]);
        }
        return $array;
    }

    public function angle($prev, $current, $next)
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


    private function getFullApi($FromLat,$FromLng,$ToLat,$ToLng )
    {
        $curl = "http://open.mapquestapi.com/directions/v2/route?key=dtKoIJL6pOrvQKs8vKJcmDx0IhGmonjF&from=" . $FromLat . "," . $FromLng . "&to=" . $ToLat . "," . $ToLng; 
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $curl);
        $responce = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($responce, true);
        return $data;
    }
}
