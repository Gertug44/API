<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'api';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-api">
    <div class="form_container">
        <form id="form" method="post">
            <input type="text" placeholder="FromLat" name="FromLat">
            <input type="text" placeholder="FromLng" name="FromLng">
            <input type="text" placeholder="ToLat" name="ToLat">
            <input type="text" placeholder="ToLng" name="ToLng">
            <input type="submit" value="Выполнить">
        </form>
    </div>



    <div class="api-content">
        <?php
        if ($_POST['FromLat'] != '' and $_POST['FromLng'] != '' and $_POST['ToLat'] != '' and $_POST['ToLat'] != '') {
            $curl = "http://open.mapquestapi.com/directions/v2/route?key=dtKoIJL6pOrvQKs8vKJcmDx0IhGmonjF&from=" . $_POST['FromLat'] . "," . $_POST['FromLng'] . "&to=" . $_POST['ToLat'] . "," . $_POST['ToLng'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $curl);

            $responce = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($responce, true);
            echo '<pre>';
            $i = 1;
            print_r($data);
        }
        ?>
    </div>
</div>