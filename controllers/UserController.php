<?php
namespace app\controllers;
use app\controllers\ApiController;
use app\components\JWTCom;
use app\models\Users;
use yii;
use app\components\Logger;


class UserController extends ApiController
{
    public $enableCsrfValidation = false;
    
    public function actionCreate()
    {
        $post=(file_get_contents("php://input"));
        $data = json_decode($post);
        $email = $data->email;
        $password = $data->password;

        Logger::getLogger("dev")->log("Начата регистрация пользователя");
        if (
            !empty($email) &&
            !empty($password))
            if (Users::find()->where(['email'=>$email])->exists())
            {
                $this->responce(400,array(
                    "status" => "fail",
                    "error" => "Указанный email существует."));
                Logger::getLogger("dev")->log("Регистрация пользователя невозможна email занят");
            }
            else
            {
                $user=new Users();
                $user -> email=$email;
                $user -> password=password_hash($password,PASSWORD_BCRYPT);
                $user -> save();
                // устанавливаем код ответа 
                $this->responce(200,array(
                    "status" => "success",
                    "message" => "Пользователь был создан."));
                Logger::getLogger("dev")->log("Пользователь успешно зарегестрировался");

            }
        // сообщение, если не удаётся создать пользователя 
        else {
            $this->responce(400,array(
                "status" => "fail",
                "error" => "Невозможно создать пользователя."));
            Logger::getLogger("dev")->log("Нет данных для регистрации");
        }
    }
    
    public function actionLogin()
    {
        $post=(file_get_contents("php://input"));
        $data = json_decode($post);
        $email = $data->email;
        $password = $data->password;

        Logger::getLogger("dev")->log("Пользователь пытается войти");

        if (empty($email) ||
        empty($password)){

            Logger::getLogger("dev")->log("Ошибка: пустые данные");
            $this->responce(400,array(
                "status" => "fail",
                "error"=>"Валенок, пароль и мыло не должны быть пустыми"));
            return;
        }
        if (Users::find()->where(['email'=>$email])->exists())
        {
            $user=Users::find()->where(['email'=>$email])->one();
            if (password_verify($password,$user->password)) {
             $jwt=JWTCom::createJWT($user);
             
             Logger::getLogger("dev")->log("Пользователь успешно вошел в систему");
                
             $this->responce(201,array(
                    "status" => "success",
                    "message" => "Успешный вход в систему.","jwt" => $jwt));
             
            }
            else{
                Logger::getLogger("dev")->log("пользователь ввел неверный пароль");
                $this->responce(400,array(
                    "status" => "fail",
                    "error"=>"Пароль неверный"));
            }
        }
        else
        {
            Logger::getLogger("dev")->log("Указанного пользователя нет в бд");
            $this->responce(400,array(
                "status" => "fail",
                "error"=>"Обладатель этого мыла еще не смешарик"));
        }
    }
    public function actionChange(){
        $post=(file_get_contents("php://input"));
        $data = json_decode($post);
        $email = $data->email;
        $newPassword = $data->newPassword;
        if (empty($email) ||
        empty($newPassword)){
            $this->responce(400,array(
                "status" => "fail",
                "error"=>"Валенок, пароль и мыло не должны быть пустыми"));
        }
        if (Users::find()->where(['email'=>$email])->exists())
        {
            // Представим что-где-то тут отправка письма на мыло
            // Костыли
            $user=Users::find()->where(['email'=>$email])->one();
            $user ->password=password_hash($params['newPassword'],PASSWORD_BCRYPT);
            $this->responce(200,array(
                "status" => "success",
                "message"=>"Пароль изменен"));

        }
        else
        {
             $this->responce(400,array(
                "status" => "fail",
                 "error"=>"Обладатель этого мыла еще не смешарик, нечего менять"));       
        }
        
    }
    
}
?>
