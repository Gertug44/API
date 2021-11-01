<?php
namespace app\controllers;
use yii\web\Controller;
use app\models\users;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use \Firebase\JWT\JWT;
class UserController extends Controller
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
            if (users::find()->where(['email'=>$email])->exists())
            {
                http_response_code(400);
                echo json_encode(array("message" => "Указанный email существует."));
            }
            else
            {
                $user=new users();
                $user -> email=$email;
                $user -> password=password_hash($password,PASSWORD_BCRYPT);
                $user -> save();
                // устанавливаем код ответа 
                http_response_code(200);        
                // покажем сообщение о том, что пользователь был создан 
                echo json_encode(array("message" => "Пользователь был создан."));

            }
        // сообщение, если не удаётся создать пользователя 
        else {
         
            // устанавливаем код ответа 
           http_response_code(400);
         
            // покажем сообщение о том, что создать пользователя не удалось 
            echo json_encode(array("message"=>"Невозможно создать пользователя."));
        }
    }
    
    public function actionLogin()
    {
        include_once "..\config\core.php";
        include_once '..\libs\php-jwt-master\src\BeforeValidException.php';
        include_once '..\libs\php-jwt-master\src\ExpiredException.php';
        include_once '..\libs\php-jwt-master\src\SignatureInvalidException.php';
        include_once '..\libs\php-jwt-master\src\JWT.php';
        $post=(file_get_contents("php://input"));
        $data = json_decode($post);
        $email = $data->email;
        $password = $data->password;

        if (empty($email) ||
        empty($password)){
            echo json_encode(array("message"=>"Валенок, пароль и мыло не должны быть пустыми"));
            return;
        }
        if (users::find()->where(['email'=>$email])->exists())
        {
            $user=users::find()->where(['email'=>$email])->one();
            if (password_verify($password,$user->password)) {
 
                $token = array(
                   "iss" => $iss,
                   "aud" => $aud,
                   "iat" => $iat,
                   "nbf" => $nbf,
                   "data" => array(
                       "id" => $user->id,
                       "email" => $user->email
                   )
                   );
             
                // код ответа 
                http_response_code(200);
             
                // создание jwt 
                $jwt = JWT::encode($token, $key);
                echo json_encode(
                    array(
                        "message" => "Успешный вход в систему.",
                        "jwt" => $jwt
                    )
                );
             
            }
            else{
                echo json_encode(array("message"=>"Пароль неверный"));
            }
        }
        else
        {
            echo json_encode(array("message"=>"Обладатель этого мыла еще не смешарик"));
        }
    }
}
?>
