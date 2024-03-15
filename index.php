<?php
// session_start();
require_once ("connect_db.php"); // подключение к БД
require_once ('pdo_functions/pdo_functions.php');



// Формируем тип перехода (Все переходы должны быть через index.php)
isset($_GET['transition'])? $transition=$_GET['transition']:$transition=0; // показывает куда переходить

switch ($transition) {
    case 10: // Разбор ВБ
        require_once ('wb_new_razbor/index_wb.php');
 
        
        break;
      
    case 50: // Автосклад
        require_once ('autosklad/start_mp.php');
 
        
        break;
    

    case 0: // основная таблица со всеми КП
//         $arr_temp = get_catalog_wb();


echo "<a href = \"?transition=50\">Автосклад </a>";
echo "<br><br>";
echo "<a href = \"?transition=10\">Разбор ВБ </a>";
echo "<br><br>";
echo "<a href = \"#\">Автосклад </a>";



die();
}



