<?php

namespace app\controllers;

use yii\web\Controller;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;
    public function actionGetapi()  
    {
        $content = array();
        if (isset($_POST['submit'])) $content = $this->getapi();
        return $this->render('getapi',['turns' => $content]);
    }
    
    public function actionFullapi()
    {
        $content = array();
        if (isset($_POST['submit'])) $content = $this->getfullapi();
        return $this->render('getapi',['turns' => $content]);
    }

    
    private function getapi()
    {   
            $curl = "http://open.mapquestapi.com/directions/v2/route?key=dtKoIJL6pOrvQKs8vKJcmDx0IhGmonjF&from=" . $_POST['FromLat'] . "," . $_POST['FromLng'] . "&to=" . $_POST['ToLat'] . "," . $_POST['ToLng'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $curl);

            $responce = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($responce, true);
            $turns = array();
            if ($data['route']['legs']==null ) return "Маршрут не найден";
                foreach ($data['route']['legs']['0']['maneuvers'] as $startpoint) 
                    array_push($turns, $startpoint['startPoint']);
            return $turns;
            
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
