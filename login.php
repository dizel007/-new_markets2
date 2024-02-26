<?php
 // Страница авторизации
   // Функция для генерации случайной строки
function generateCode($length = 6)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
    $code = "";
    $clen = strlen($chars) - 1;
    while (strlen($code) < $length) {
        $code .= $chars[mt_rand(0, $clen)];
    }
    return $code;
}
// Функция проверяет IP адрес пользователя  и обновляет его в БД и задержка 3 секунды
function FindUserIP ($pdo, $new_data) {
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = @$_SERVER['REMOTE_ADDR'];
     
    if(filter_var($client, FILTER_VALIDATE_IP)) $ip = $client;
    elseif(filter_var($forward, FILTER_VALIDATE_IP)) $ip = $forward;
    else $ip = $remote;
  
// Записываем в БД новый хеш авторизации и IP
$stmt = $pdo->prepare("UPDATE `users` SET `user_ip` = :user_ip WHERE `user_id` = :user_id");
$stmt->execute(array('user_ip' => $ip, 
'user_id' => $new_data['user_id']));

// sleep(4); //********************************************** Задержка ***************************************************************** */
    // echo $ip;
    // die();
}

// Соединямся с БД
require_once ("main_info.php");

try {  
    $pdo = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $user, $password);
    $pdo->exec('SET NAMES utf8');
    } catch (PDOException $e) {
    print "Не смогли подключиться к БД: " . $e->getMessage();  die();
    }
 
if (isset($_POST['submit'])) {
    $login = $_POST['login'];
    $input_password =  md5(md5($_POST['password']));
    // Вытаскиваем из БД запись, у которой логин равняеться введенному
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_login='" . $login . "' LIMIT 1");
    $stmt->execute([]);
    $udata = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
if (isset($udata[0])) { // Проверяем, достали ли ЛОГИН из БД
$new_data = call_user_func_array('array_merge', $udata); // Уменьшаем уровень вложенности массива 



// echo "<pre>";
// print_r($udata);
// echo "<pre>";



// Проверяем IP
FindUserIP ($pdo, $new_data);

        // Сравниваем пароли
        if ($new_data['user_password'] === $input_password) {
            // Генерируем случайное число и шифруем его
            $hash = md5(generateCode(10));
            // Записываем в БД новый хеш авторизации и IP
            $stmt = $pdo->prepare("UPDATE `users` SET `user_hash` = :user_hash WHERE `user_id` = :user_id");
            $stmt->execute(array('user_hash' => $hash, 
            'user_id' => $new_data['user_id']));
            // Ставим куки
            setcookie("id", $new_data['user_id'], time() + 60 * 60 * 24, "/");
            setcookie("hash", $hash, time() + 60 * 60 * 24, "/", null, null, true);
            setcookie("user_name", $new_data['user_login'], time() + 60 * 60 * 24, "/", null, null, true);
            // Переадресовываем браузер на страницу проверки нашего скрипта
            header("Location: index.php");
            exit();
        } else {
            $subject_theme="Кто то неверно ввел пароль";
            require('mailer/alarm_mail_message.php');
            print "Вы ввели неправильный логин/пароль";
        
        }
    } else {
        $subject_theme="Кто то неверно ввел логин";
        require('mailer/alarm_mail_message.php');
        print "Вы ввели неправильный логин/пароль!"; 
    }

 }
    
?>


<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="css/login.css">

    <!-- <title>Инициализация польззователя</title> -->
</head>

<body>
    <div class="container">
        <div class="row">
        <div class="col-3 w-30 mx-auto shadow-lg loginform">
            <form method="POST">
                <br>
                <label for="exampleFormControlInput1" class="form-label">Логин</label>
                    <input class="form-control"  name="login" type="text" required>
                <label for="exampleFormControlInput1" class="form-label">Пароль</label>
                    <input class="form-control"  name="password" type="password" required>
                <br>
                

                <div class = "center">
                    <input class="btn btn-outline-primary" name="submit" type="submit" value="Войти">
                </div>
            </form>
          </div>
        </div>
    </div>
</body>
</html>