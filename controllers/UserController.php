<?php
namespace app\controllers;
use app\controllers\ApiController;
use app\components\JWTCom;
use app\models\Users;
use app\models\ChangePassForm;
use yii;
class UserController extends ApiController
{
    public $enableCsrfValidation = false;
    
    public function actionCreate()
    {
        $post=(file_get_contents("php://input"));
        $data = json_decode($post);
        $email = $data->email;
        $password = $data->password;

        if (
            !empty($email) &&
            !empty($password))
            if (Users::find()->where(['email'=>$email])->exists())
            {
                $this->responce(400,array("message" => "Указанный email существует."));
            }
            else
            {
                $user=new Users();
                $user -> email=$email;
                $user -> password=password_hash($password,PASSWORD_BCRYPT);
                $user -> save();
                // устанавливаем код ответа 
                $this->responce(200,array("message" => "Пользователь был создан."));

            }
        // сообщение, если не удаётся создать пользователя 
        else {
            $this->responce(400,array("message" => "Невозможно создать пользователя."));
        }
    }
    
    public function actionLogin()
    {
        $post=(file_get_contents("php://input"));
        $data = json_decode($post);
        $email = $data->email;
        $password = $data->password;

        if (empty($email) ||
        empty($password)){
            $this->responce(400,array("message"=>"Валенок, пароль и мыло не должны быть пустыми"));
            return;
        }
        if (Users::find()->where(['email'=>$email])->exists())
        {
            $user=Users::find()->where(['email'=>$email])->one();
            if (password_verify($password,$user->password)) {
             $jwt=JWTCom::createJWT($user);

                $this->responce(201,array("message" => "Успешный вход в систему.","jwt" => $jwt));
             
            }
            else{
                $this->responce(400,array("message"=>"Пароль неверный"));
            }
        }
        else
        {
            $this->responce(400,array("message"=>"Обладатель этого мыла еще не смешарик"));
        }
    }
    public function actionChange(){
        $post=(file_get_contents("php://input"));
        $data = json_decode($post);
        $email = $data->email;
        $newPassword = $data->newPassword;
        if (Users::find()->where(['email'=>$email])->exists())
        {
            $user=Users::find()->where(['email'=>$email])->one();
            $user ->password=password_hash($params['newPassword'],PASSWORD_BCRYPT);
            $this->responce(200,array("message"=>"Пароль изменен"));

        }
        else
        {
             $this->responce(400,array("message"=>"Обладатель этого мыла еще не смешарик, нечего менять"));       
        }
        
    }
    
}
?>
