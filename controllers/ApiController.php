<?php

namespace app\controllers;

use yii\web\Controller;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionGetapi()
    {
        $content = array();
        if (isset($_POST['submit'])) $content = $this->getturns();
        return $this->render('getapi', ['turns' => $content]);
    }

    public function actionFullapi()
    {
        $content = array();
        if (isset($_POST['submit'])) $content = $this->getfullapi();
        return $this->render('getapi', ['turns' => $content]);
    }


    private function getturns()
    {
        $curl = "http://open.mapquestapi.com/directions/v2/route?key=dtKoIJL6pOrvQKs8vKJcmDx0IhGmonjF&from=" . $_POST['FromLat'] . "," . $_POST['FromLng'] . "&to=" . $_POST['ToLat'] . "," . $_POST['ToLng'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $curl);

        $responce = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($responce, true);
        $turns = array();
        if ($data['route']['legs'] == null) return "Маршрут не найден";
        foreach ($data['route']['legs']['0']['maneuvers'] as $startpoint)
            $turns[] = $startpoint['startPoint'];
            $turns = $this->InsertIntoArr($turns);
        return $turns;
    }

    private function InsertIntoArr($array)
    {
        for($i=1;$i<=count($array)-2;$i++)
        {
            $prev = $array[$i-1];
            $current = $array[$i];
            $next = $array[$i+1];
            $array[$i]['angle'] = $this->angle($prev,$current,$next);
        }
         return $array;
    }

    public function angle($prev, $current, $next)
    {
        $vectorX = array($current['lat']-$prev['lat'] , $current['lng']-$prev['lng']);
        $vectorY = array($next['lat']-$current['lat'] , $next['lng']-$current['lng']);
        $scalarmul = $vectorX[0] * $vectorY[0] + $vectorX[1] * $vectorY[1];
        $LengthX = sqrt(pow($vectorX[0],2) + pow($vectorX[1],2));
        $LengthY = sqrt(pow($vectorY[0],2) + pow($vectorY[1],2)); 
        return round((($scalarmul / ($LengthX*$LengthY))),4);
    }


    private function getfullapi()
    {
        $curl = "http://open.mapquestapi.com/directions/v2/route?key=dtKoIJL6pOrvQKs8vKJcmDx0IhGmonjF&from=" . $_POST['FromLat'] . "," . $_POST['FromLng'] . "&to=" . $_POST['ToLat'] . "," . $_POST['ToLng'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $curl);

        $responce = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($responce, true);
        return $data;
    }
}
