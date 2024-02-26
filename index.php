<?php
// session_start();
require_once ("connect_db.php"); // подключение к БД
require_once ('pdo_functions/pdo_functions.php');



// Формируем тип перехода (Все переходы должны быть через index.php)
isset($_GET['transition'])? $transition=$_GET['transition']:$transition=0; // показывает куда переходить

switch ($transition) {
    
    case 50: // Автосклад
        require_once ('autosklad/start_mp.php');
 
        
        break;
    

    case 0: // основная таблица со всеми КП
//         $arr_temp = get_catalog_wb();

die();
}



