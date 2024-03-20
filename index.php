<?php
// session_start();
require_once("connect_db.php"); // подключение к БД
require_once('pdo_functions/pdo_functions.php');



// Формируем тип перехода (Все переходы должны быть через index.php)
isset($_GET['transition']) ? $transition = $_GET['transition'] : $transition = 0; // показывает куда переходить

switch ($transition) {
    case 10: // Разбор ВБ
        require_once('wb_new_razbor/index_wb.php');

        break;

    case 11: // Разбор ВБ ИП
        require_once('wb_new_razbor/index_wbip.php');

        break;

    case 20: // Разбор OZON OOO
        require_once('ozon_razbor/index_ozon.php');
        break;

    case 21: // Разбор OZON IP
        require_once('ozon_razbor/index_ozon_ip.php');
        break;

    case 31: // Разбор Yandex
        require_once('yandex_razbor/index_yandex.php');
        break;

    case 50: // Автосклад
        require_once('autosklad/start_mp.php');


        break;

        // 
    case 0: // основная таблица со всеми КП
        //         $arr_temp = get_catalog_wb();


        echo "<a href = \"?transition=50\">Автосклад </a>";
        echo "<br><br>";
        echo "<a href = \"?transition=10\">Разбор ВБ Анмакс</a>";
        echo "<br><br>";
        echo "<a href = \"?transition=11\">Разбор ВБ ИП</a>";
        echo "<br><br>";
        echo "<a href = \"?transition=20\">Разбор ОЗОН Анмакс</a>";
        echo "<br><br>";
        echo "<a href = \"?transition=21\">Разбор ОЗОН ИП Зел</a>";
        echo "<br><br>";
        echo "<a href = \"?transition=31\">Разбор ЯндексМаркет ООО ТД Анмакс</a>";
        echo "<br><br>";


        die();
}
