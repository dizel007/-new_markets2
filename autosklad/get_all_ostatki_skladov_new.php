<?php
require_once '../connect_db.php';
require_once '../pdo_functions/pdo_functions.php';

require_once '../libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
require_once "../mp_functions/ozon_api_functions.php";
require_once "../mp_functions/ozon_functions.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";

require_once "../autosklad/functions/parce_excel_sklad_json.php";
require_once "../autosklad/functions/function_autosklad.php";
require_once "../autosklad/functions/write_html_table.php";

echo '<link rel="stylesheet" href="css/main_table.css">';

echo "<pre>";
 
if (isset($_GET['return'])) {
    $return_after_update = $_GET['return'];
} else {
    $return_after_update = 0;
}

if ($return_after_update == 777) {
    
    $arr_article_items = json_decode(file_get_contents("uploads/array_items.json"));
    // echo "<pre >";
    // print_r ($arr_article_items);
    // die();

} else {
$uploaddir = "uploads/";
if (isset($_FILES['file_excel'])) {
$uploadfile = $uploaddir . basename( $_FILES['file_excel']['name']);

    if(move_uploaded_file($_FILES['file_excel']['tmp_name'], $uploadfile))
            {
            echo "Файл с остатками товаров, УСПЕШНО ЗАГРУЖЕН<br>";
            }
            else
            {
            die ("DIE ОШИБКА при загрузке файла");
    }
} else {
    die ("DIE НЕТ ЗАГРУЖАЕМОГО файла");
}
// $xls = PHPExcel_IOFactory::load('temp_sklad/temp.xlsx');
$xls = PHPExcel_IOFactory::load($uploadfile);
$arr_new_ostatoki_MP =  Parce_excel_1c_sklad ($xls) ; // парсим Загруженный файл и формируем JSON архив для дальнейшей работы
}
// // Оставляем массив ключ (артикул) значение остаток
// foreach ($arr_article_items as $key=>$itemss ) {
//     foreach ($itemss as $mp_key=>$ostatok) {
//         if ($mp_key == 'MP') {
//             $arr_new_ostatoki_MP[mb_strtolower($key)] = $ostatok ; // массив остатков из 1С
//         }
//     }
    

// }

// print_r($arr_new_ostatoki_MP);
// die();






// Получаем все токены
$arr_tokens = get_tokens($pdo);
// ВБ АНМАКС
$token_wb = $arr_tokens['wb_anmaks']['token'];
// ВБ ГОР
$token_wb_ip = $arr_tokens['wb_ip_goryachev']['token'];
// ОЗОН АНМКАС
$client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
$token_ozon = $arr_tokens['ozon_anmaks']['token'];
// озон ИП зел
$client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
$token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

// Доставем информацию по складам ****** АКТИВНЫМ СКЛАДАМ ******
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам

// Находим общее количество проценьлв, которое нужно распределить
$all_procent_for_all_shops=0;
foreach ($sklads as $sklad) {
$all_procent_for_all_shops+=$sklad['procent']; // сумма всех процентов для распрделения
}

$arr_need_ostatok = get_min_ostatok_tovarov($pdo); // массив с утвержденным неснижаемым остатком


// Вся продаваемая номенклатура
$arr_all_nomenklatura = select_all_nomenklaturu($pdo);

// print_r($arr_all_nomenklatura);

// Формируем каталоги товаров
$wb_catalog   = get_catalog_tovarov_v_mp('wb_anmaks', $pdo);
$wbip_catalog = get_catalog_tovarov_v_mp('wb_ip_goryachev', $pdo); // фомируем каталог
$ozon_catalog = get_catalog_tovarov_v_mp('ozon_anmaks', $pdo); // получаем озон каталог
$ozon_ip_catalog = get_catalog_tovarov_v_mp('ozon_ip_zel', $pdo); // получаем озон каталог


// Формируем массив в номенклатурой, с учетом того, что один товар можнт продаваться под разным артикулом на Маркете


/* *****************************      Получаем Фактические остатки с ВБ *****************************/
$wb_catalog = get_ostatki_wb ($token_wb, $wb_catalog, $sklads['wb_anmaks']['warehouseId']);
//*****************************      Достаем фактически заказанные товары  *****************************
$wb_catalog = get_new_zakazi_wb ($token_wb, $wb_catalog);


/* *****************************      Получаем Фактические остатки с ВБ ИП *****************************/
$wbip_catalog = get_ostatki_wb ($token_wb_ip, $wbip_catalog, $sklads['wb_ip_goryachev']['warehouseId']); // цепляем остатки 
//*****************************      Достаем фактически заказанные товары  WB IP *****************************
$wbip_catalog = get_new_zakazi_wb ($token_wb_ip, $wbip_catalog);


//***************************** Получаем Фактические остатки с OZON *****************************
$ozon_catalog = get_ostatki_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары OZON *****************************
$ozon_catalog = get_new_zakazi_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепляем продажи

//***************************** Получаем Фактические остатки с OZON *****************************
$ozon_ip_catalog = get_ostatki_ozon ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары OZON *****************************
$ozon_ip_catalog = get_new_zakazi_ozon ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog); // цепляем продажи

// var_dump($ozon_ip_catalog);

// print_r ($ozon_ip_catalog);
// die();

//*****************************  *************

// Добавляем в каталог процент распрделения и остаток из 1С для магазина Озон ООО 
$wb_catalog      = get_db_procent_magazina ($wb_catalog, $sklads, 'wb_anmaks', $arr_new_ostatoki_MP);
$wbip_catalog    = get_db_procent_magazina ($wbip_catalog, $sklads, 'wb_ip_goryachev', $arr_new_ostatoki_MP);
$ozon_catalog    = get_db_procent_magazina ($ozon_catalog, $sklads, 'ozon_anmaks', $arr_new_ostatoki_MP);
$ozon_ip_catalog = get_db_procent_magazina ($ozon_ip_catalog, $sklads, 'ozon_ip_zel', $arr_new_ostatoki_MP);

//*****************************  Формируем массив из всех каталогов  *****************************

$all_catalogs[]= $wb_catalog;
$all_catalogs[]= $wbip_catalog;
$all_catalogs[]= $ozon_catalog;
$all_catalogs[]= $ozon_ip_catalog;

//*****************************  получаем массив (артикул - кол-во проданного товара  *****************************
$arr_sell_tovari = make_array_all_sell_tovarov($all_catalogs);

// print_r($arr_sell_tovari);
// die();
// // выводим шапку таблицы ВБ
write_table_shapka('update_all_markets.php');
write_BODY_table ($wb_catalog, $all_catalogs, $arr_sell_tovari ) ;
// write_BODY_table ($wb_catalog, $wb_catalog, $wbip_catalog, $ozon_catalog , $sklads , $arr_sell_tovari ) ;

// // // выводим шапку таблицы ВБ ИП ГОР
write_table_shapka('#');
write_BODY_table ($wbip_catalog, $all_catalogs, $arr_sell_tovari ) ;
// write_BODY_table ($wbip_catalog, $wb_catalog, $wbip_catalog, $ozon_catalog , $sklads , $arr_sell_tovari ) ;

// // выводим шапку таблицы ОЗОН ООО
write_table_shapka('#');
write_BODY_table ($ozon_catalog, $all_catalogs, $arr_sell_tovari ) ;
// write_BODY_table ($ozon_catalog, $wb_catalog, $wbip_catalog, $ozon_catalog , $sklads , $arr_sell_tovari ) ;

// выводим шапку таблицы ОЗОН ООО
write_table_shapka('#');
write_BODY_table ($ozon_ip_catalog, $all_catalogs, $arr_sell_tovari ) ;
// write_BODY_table ($ozon_ip_catalog, $wb_catalog, $wbip_catalog, $ozon_catalog , $sklads , $arr_sell_tovari ) ;



die();