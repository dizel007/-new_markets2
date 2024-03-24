<?php

echo  "ПРОШЛИ КОННЕКТ<br>";
require_once ("main_info.php");

// ************************************** PHP EXCEL  ***********************************
require_once 'libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once 'libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once 'libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';


require_once 'pdo_functions/pdo_functions.php'; // подключаем функции  взаимодейцстя  с БД
 
      try {  
        $pdo = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $user, $password);
        $pdo->exec('SET NAMES utf8');
        } catch (PDOException $e) {
          print "Has errors: " . $e->getMessage();  die();
        }

// *************   проверяем зашел ли пользователь с паролем  ****************************************

if (isset($_COOKIE['id']) and isset($_COOKIE['hash'])) // Проверяем зарегистрирован ли пользователь
{   
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_hash='" . $_COOKIE['hash'] . "' LIMIT 1");
    $stmt->execute([]);
    $userdata_temp = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $userdata =  $userdata_temp[0];     
   
   
    // Получаем все токены
    $arr_tokens = get_tokens($pdo);
    // ВБ АНМАКС
    $token_wb = $arr_tokens['wb_anmaks']['token'];
    // ВБ ZEL
    $token_wb_ip = $arr_tokens['wb_ip_zel']['token'];
    // ОЗОН АНМКАС
    $client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon = $arr_tokens['ozon_anmaks']['token'];
    // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];



// ***************   проверяем введеный хэш пароля с тем, что храниться в БД  ***************************
    if(($userdata['user_hash'] !== $_COOKIE['hash']) or ($userdata['user_id'] !== $_COOKIE['id']))
    {
        header("Location: login.php"); exit();
    }
  } else {
    header("Location: login.php"); exit();
  }

// *******************   Обновляем каждый раз Куку  *******************************
$hash= $_COOKIE['hash'];
$user_id_cook = $_COOKIE['id'];
setcookie("id", $user_id_cook, time() + 60 * 60 * 24, "/");
setcookie("hash", $hash, time() + 60 * 60 * 24, "/", null, null, true); 