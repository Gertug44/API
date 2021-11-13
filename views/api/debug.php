<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\captcha\Captcha;
?>

<div class="row">
    <div class="col-lg-5">

        <div class="container">
            <form id="form" action="" method="post">
                <input type="text" placeholder="FromLat" name="FromLat">
                <input type="text" placeholder="FromLng" name="FromLng">
                <input type="text" placeholder="ToLat" name="ToLat">
                <input type="text" placeholder="ToLng" name="ToLng">
                <input type="submit" value="Выполнить" name="submit">
            </form>
        </div>

    </div>
    <div class="container">
        <div class="row">
            <div class="cl-lg-12">
            <?php
            echo "<pre>";
                $turns!=null? print_r($turns) : print("Координаты не заданы");
            echo "<pre>";
            ?>
            </div>
        </div>
    </div>
</div>