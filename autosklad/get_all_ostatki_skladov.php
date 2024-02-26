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


echo '<link rel="stylesheet" href="css/main_table.css">';


 
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
$arr_article_items =  Parce_excel_1c_sklad ($xls) ; // парсим Загруженный файл и формируем JSON архив для дальнейшей работы
}


echo "<pre>";





// Получаем все токены
$arr_tokens = get_tokens($pdo);
// ВБ АНМАКС
$token_wb = $arr_tokens['wb_anmaks']['token'];
// ВБ ГОР
$token_wb_ip = $arr_tokens['wb_ip_goryachev']['token'];
// ОЗОН АНМКАС
$client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
$token_ozon = $arr_tokens['ozon_anmaks']['token'];

// Доставем информацию по складам 
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам
$arr_need_ostatok = get_min_ostatok_tovarov($pdo); // массив с утвержденным неснижаемым остатком


// Вся продаваемая номенклатура
$arr_all_nomenklatura = select_all_nomenklaturu($pdo);

// print_r($arr_all_nomenklatura);

// Формируем три каталога товаров
$wb_catalog   = get_catalog_tovarov_v_mp('wb_anmaks', $pdo);
$wbip_catalog = get_catalog_tovarov_v_mp('wb_ip_goryachev', $pdo); // фомируем каталог
$ozon_catalog = get_catalog_tovarov_v_mp('ozon_anmaks', $pdo); // получаем озон каталог
// Формируем массив в номенклатурой, с учетом того, что один товар можнт продаваться под разным артикулом на Маркете



/* *****************************      Получаем Фактические остатки с ВБ *****************************/

$wb_catalog = get_ostatki_wb ($token_wb, $wb_catalog, $sklads['wb_anmaks']['warehouseId']);
//*****************************      Достаем фактически заказанные товары  *****************************
$wb_catalog = get_new_zakazi_wb ($token_wb, $wb_catalog);

foreach ($arr_all_nomenklatura as &$nomenclatura) {
    foreach ($ozon_catalog as $ozon_items) {

        /// ЕСЛИ есть дубль товара, то добавляем артикул
        if (($ozon_items['article_double']) == true) {
            $arr_artrikules[mb_strtolower($ozon_items['main_article'])]['ozon_anmaks'][$ozon_items['ozon_article']] = $ozon_items['fbs'];
        } else {
            $arr_artrikules[mb_strtolower($ozon_items['main_article'])]['ozon_anmaks'][$ozon_items['ozon_article']] = $ozon_items['fbs'];
        }

    
    }

    //

    foreach ($wb_catalog as $wb_items) {
        $arr_artrikules[mb_strtolower($wb_items['main_article'])]['wb_anmaks'][$wb_items['wb_article']] = $wb_items['fbs'];
    }

    foreach ($wbip_catalog as $wbip_items) {
        $arr_artrikules[mb_strtolower($wbip_items['main_article'])]['wb_ip_goryachev'][$wbip_items['wb_article']] = $wbip_items['fbs'];
    }
    
    foreach ($ozon_catalog as $ozon_items) {
        $arr_artrikules[mb_strtolower($ozon_items['main_article'])]['ozon_anmaks'][$ozon_items['ozon_article']] = $ozon_items['fbs'];
    }

}

// print_r($arr_artrikules);
// die(); 
echo  "<br>************************<br>";
$wbip_catalog = get_ostatki_wb ($token_wb_ip, $wbip_catalog, $sklads['wb_ip_goryachev']['warehouseId']); // цепляем остатки 
// echo "****************************";
// print_r($wbip_catalog);
// echo "****************************";

//*****************************      Достаем фактически заказанные товары  WB IP *****************************
$wbip_catalog = get_new_zakazi_wb ($token_wb_ip, $wbip_catalog);





//***************************** Получаем Фактические остатки с OZON *****************************

$ozon_catalog = get_ostatki_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары OZON *****************************
$ozon_catalog = get_new_zakazi_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепляем продажи



// print_r($ozon_catalog);
// die('ddddddddkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkdddd');

//*********************************************************************************
// Формируем массив из 1С файла где Количество товара только на складе МАРКЕТПЛЭЙС


foreach ($arr_article_items as $key_1=>$items) {
    foreach ($items as $key_2=>$item) {
    if ($key_2 == 'MP') {
        $key_1 = mb_strtoupper($key_1);
        $arr_mp_items[$key_1] = $item ;
    }
    }
}

// вычитаем из 1С данных уже проданные товары 
$arr_mp_items_without_sell_tovar = $arr_mp_items; // из этого массива выберем проданные товары
foreach ($arr_mp_items_without_sell_tovar as $key=>&$items) {
    // ВЫчитаем товары проданные на ВБ
    foreach ($wb_catalog as $zz) {
        if ((mb_strtoupper($zz['main_article']) == mb_strtoupper($key)) && isset($zz['sell_count'])) {
            $items = $items - $zz['sell_count'];
            break 1;
         }
    }
// ВЫчитаем товары проданные на ВБ ИП
    foreach ($wbip_catalog as $zz) {
        if ((mb_strtoupper($zz['main_article']) == mb_strtoupper($key)) && isset($zz['sell_count'])) {
            $items = $items - $zz['sell_count'];
            break 1;
         }
    }
// ВЫчитаем товары проданные на ОЗОН
    foreach ($ozon_catalog as $zz) {
        if ((mb_strtoupper($zz['main_article']) == mb_strtoupper($key)) && isset($zz['sell_count'])) {
            $items = $items - $zz['sell_count'];
            break 1;
         }
    }


}

/***************************************************************************************************** */
// перебираем массив товаров с МП (с учетом проданных уже товаров)




foreach ($arr_mp_items_without_sell_tovar as $key=> $kolvo_tovara) {

    foreach ($wb_catalog as $zz) {
        if (mb_strtoupper($zz['main_article']) == mb_strtoupper($key)){
            $WB_proc_ =$sklads['wb_anmaks']['procent'];
            break 1;
        } else {
            $WB_proc_ = 0;
        }
    }

    foreach ($wbip_catalog as $zz1) {
        if (mb_strtoupper($zz1['main_article']) == mb_strtoupper($key)){
            $WBIP_proc_ = $sklads['wb_ip_goryachev']['procent'];
            break 1;
        } else {
            $WBIP_proc_ = 0;
        }
    }
    foreach ($ozon_catalog as $zz2) {
        if (mb_strtoupper($zz2['main_article']) == mb_strtoupper($key)){
            $OZON_proc_ = $sklads['ozon_anmaks']['procent'];
            break 1;
        } else {
            $OZON_proc_ = 0;
        }
    }
$all_procents = $WB_proc_ + $WBIP_proc_ + $OZON_proc_;

// Количество товаров после всего распределения для ВБ
foreach ($wb_catalog as $wb_items) {
         if (mb_strtoupper($wb_items['main_article']) == mb_strtoupper($key)){ // все переводим в верхний регистр
            $arr_wb[mb_strtoupper($key)] = floor($kolvo_tovara / $all_procents * $sklads['wb_anmaks']['procent']);
            } 
        }

// Количество товаров после всего распределения для  ВБ ИП
foreach ($wbip_catalog as $wbip_items) {
    if (mb_strtoupper($wbip_items['main_article']) == mb_strtoupper($key)){ // все переводим в верхний регистр
        $arr_wbip[mb_strtoupper($key)] = floor($kolvo_tovara / $all_procents * $sklads['wb_ip_goryachev']['procent']);
     } 
    }

 // Количество товаров после всего распределения для  OZON
foreach ($ozon_catalog as $ozon_items) {
    if (mb_strtoupper($ozon_items['main_article']) == mb_strtoupper($key)){ // все переводим в верхний регистр
        $arr_ozon[$key] = floor($kolvo_tovara / $all_procents * $sklads['ozon_anmaks']['procent']);
         }
    }


}



// die('rrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr');

echo <<<HTML
<div class="center_form">
<form action="update_all_markets.php" method="post">
<table>
<tr class="prods_table">
    <td width="30">пп</td>
    <td width="150">артикул</td>
    <td>Oстатки<br>из 1С</td>
    <td>Oстатки<br>из 1С<br>с проданным</td>
<!-- ************************** WB    **********************************-->    
    <td>Кол-во<br>продано<br>WB</td>
    <td>Кол-во<br>на WB<br>(Остаток)</td>
    <td>Рекоменд<br>(Будуший<br>остаток)</td>
    <td>WB</td>
<!-- ************************** WBIP  **********************************-->
    <td>Кол-во<br>продано<br>WBIP</td>
    <td>Кол-во<br>на WBIP<br>(Остаток)</td>
    <td>Рекоменд<br>(Будуший<br>остаток)</td>
    <td>WB<br>IP</td>
<!-- ************************** OZON   **********************************-->
    <td>Кол-во<br>продано<br>OZON</td>
    <td>Кол-во<br>на OZON<br>(Остаток)</td>
    <td>Рекоменд<br>(Будуший<br>остаток)</td>
    <td>OZON</td>
<!-- ************************** Остатки на складе   **********************************-->
<td>-****-</td>
<td>Остатки<br>после<br>распределения</td>    

<!-- ************************** Остатки на складе   **********************************-->
<td>Утвержд<br>неснижаемый<br>остаток</td>   
<td>Требуется<br>пополнить<br>ЗАЯВКА</td>   

</tr>
HTML;

unset ($items); // чистим эту переменную

// Во всех каталогах вместо порядкового номера в массиве подставляем АРТКУЛ (ключ - артикул)
foreach ($wb_catalog as $mmmm) {
    $new_wb_catalog[mb_strtoupper($mmmm['main_article'])] = $mmmm;
}
foreach ($wbip_catalog as $mmmm2) {
    $new_wbip_catalog[mb_strtoupper($mmmm2['main_article'])] = $mmmm2;
}

foreach ($ozon_catalog as $mmmm3) {
    $new_ozon_catalog[mb_strtoupper($mmmm3['main_article'])] = $mmmm3;
}



// print_r($arr_mp_items);
// Формировать данные для таблицы
$pp=0;
foreach ($arr_mp_items as $key => $items) {
    $pp++;
    $quantity_1c = $arr_mp_items[$key];
    $quantity_1c_without_sell_tovar = $arr_mp_items_without_sell_tovar[$key];

/***************************** Данные по ВБ ************************************* */
    // количество проданных товаров
    isset($new_wb_catalog[$key]['sell_count'])?$sell_count_wb = $new_wb_catalog[$key]['sell_count']:$sell_count_wb = 0;
    ($sell_count_wb > 0)?$wb_sell_tovar_color="sell_tovar_color": $wb_sell_tovar_color=""; // подсвечиваем проданные товары
    // Нынешний остаток товаров на WB
   isset($new_wb_catalog[$key]['quantity'])?$quantity_wb = $new_wb_catalog[$key]['quantity']:$quantity_wb = "-";
    // Будущий остаток товаров на WB
   isset($arr_wb[$key])?$quantity_new_wb = $arr_wb[$key] - 1:$quantity_new_wb = "-";
    // Доставем баркод для обновления остатков товаров на WB
    isset($new_wb_catalog[$key])?$wb_barCode = $new_wb_catalog[$key]['barcode']:$wb_barCode = "";

   // Проверяем текущий остаток, чтобы он был не меньше Будущего
   ($quantity_wb == 0)? $wb_css_ostatok = "zero_alarm_color": $z = 1 ; // смотрим, когда количество товара равно 0 
   ($quantity_new_wb < $quantity_wb)? $wb_css_ostatok = "alarm_color": $wb_css_ostatok = "green_color" ; // факт кол-во товара больше будущего
   ($quantity_new_wb <> $quantity_wb)? $wb_check_point = 1: $wb_check_point = 0 ; // смотрим, где штатное обновление остатков товара
   (!is_numeric($quantity_wb))?$wb_check_point = 999: $z=1 ; // если товара не существует, то блокируем галочку
   ($quantity_wb < 6)?$wb_css_ostatok = "orange_color":  $z = 1; // подсвечиваем, где мало товара
   ($quantity_wb =="-")?$wb_css_ostatok = "":  $z = 1; // убираем окраску, где где товар не продается


/***************************** Данные по ВБ ИП ************************************* */
    // количество проданных товаров
    isset($new_wbip_catalog[$key]['sell_count'])?$sell_count_wbip = $new_wbip_catalog[$key]['sell_count']:$sell_count_wbip = 0;
    ($sell_count_wbip > 0)?$wbip_sell_tovar_color="sell_tovar_color": $wbip_sell_tovar_color=""; // подсвечиваем проданные товары
    // Нынешний остаток товаров на WB
    isset($new_wbip_catalog[$key]['quantity'])?$quantity_wbip = $new_wbip_catalog[$key]['quantity']:$quantity_wbip = "-";
    // Будущий остаток товаров на WB
    isset($arr_wbip[$key])?$quantity_new_wbip = $arr_wbip[$key] - 1:$quantity_new_wbip = "-";
   // Доставем баркод для обновления остатков товаров на WB
   isset($new_wbip_catalog[$key])?$wbip_barCode = $new_wbip_catalog[$key]['barcode']:$wbip_barCode = "";

    // Проверяем текущий остаток, чтобы он был не меньше Будущего
   ($quantity_wbip == 0)? $wbip_css_ostatok = "zero_alarm_color": $z = 1 ; // смотрим, когда количество товара равно 0 
   ($quantity_new_wbip < $quantity_wbip)? $wbip_css_ostatok = "alarm_color": $wbip_css_ostatok = "green_color" ; // факт кол-во товара больше будущего
   ($quantity_new_wbip <> $quantity_wbip)? $wbip_check_point = 1: $wbip_check_point = 0 ; // смотрим, где штатное обновление остатков товара
   (!is_numeric($quantity_wbip))?$wbip_check_point = 999: $z=1 ; // если товара не существует, то блокируем галочку
   ($quantity_wbip < 6)?$wbip_css_ostatok = "orange_color":  $z = 1; // подсвечиваем, где мало товара
   ($quantity_wbip =="-")?$wbip_css_ostatok = "":  $z = 1; // убираем окраску, где где товар не продается



/***************************** Данные по ОЗОН ************************************* */
    // количество проданных товаров
    isset($new_ozon_catalog[$key]['sell_count'])?$sell_count_ozon = $new_ozon_catalog[$key]['sell_count']:$sell_count_ozon = 0;
    ($sell_count_ozon > 0)?$ozon_sell_tovar_color="sell_tovar_color": $ozon_sell_tovar_color=""; // подсвечиваем проданные товары
    // Нынешний остаток товаров на WB
    isset($new_ozon_catalog[$key]['quantity'])?$quantity_ozon = $new_ozon_catalog[$key]['quantity']:$quantity_ozon = "-";
    // Будущий остаток товаров на WB
    isset($arr_ozon[$key])?$quantity_new_ozon = $arr_ozon[$key] - 1 :$quantity_new_ozon = "-";
   // Нынешний остаток товаров на WB
   isset($new_ozon_catalog[$key])?$ozon_barCode = $new_ozon_catalog[$key]['sku']:$ozon_barCode = "-";
 

// Проверяем текущий остаток, чтобы он был не меньше Будущего
   ($quantity_ozon == 0)? $ozon_css_ostatok = "zero_alarm_color": $z = 1 ; // смотрим, когда количество товара равно 0 
   ($quantity_new_ozon < $quantity_ozon)? $ozon_css_ostatok = "alarm_color": $ozon_css_ostatok = "green_color" ; // факт кол-во товара больше будущего
   ($quantity_new_ozon <> $quantity_ozon)? $ozon_check_point = 1: $ozon_check_point = 0 ; // смотрим, где штатное обновление остатков товара
   (!is_numeric($quantity_ozon))?$ozon_check_point = 999: $z = 1 ; // если товара не существует, то блокируем галочку
   ($quantity_ozon < 6)?$ozon_css_ostatok = "orange_color":  $z = 1; // подсвечиваем, где мало товара
   ($quantity_ozon =="-")?$ozon_css_ostatok = "":  $z = 1; // убираем окраску, где где товар не продается



// Получаем  Количество нераспределенного товара
(is_numeric($quantity_new_wb))? $nr_ostatki_wb = $quantity_new_wb: $nr_ostatki_wb = 0;
(is_numeric($quantity_new_wbip))? $nr_ostatki_wbip = $quantity_new_wbip: $nr_ostatki_wbip = 0;
(is_numeric($quantity_new_ozon))? $nr_ostatki_ozon = $quantity_new_ozon: $nr_ostatki_ozon = 0;


// Получаем количество нераспределеггых товаров
$neraspred_ostatki_tovarov = $quantity_1c_without_sell_tovar - $nr_ostatki_wb - $nr_ostatki_wbip - $nr_ostatki_ozon;

// Выводим минимально допустимый остаток товаров
$min_ostatok_tovarov = $arr_need_ostatok[$key];

// формируем количество товаров для Заявки в снабжение
if ($quantity_1c_without_sell_tovar < $min_ostatok_tovarov) {
$tovari_k_popolneniu = $min_ostatok_tovarov - $quantity_1c_without_sell_tovar;
$need_some_tovarov = "need_some_tovarov";
} else {
    $tovari_k_popolneniu =0; 
    $need_some_tovarov = "";
}

echo <<<HTML
<tr class="prods_table">
    <td>$pp</td>
    <td>$key</td>
    <td class="text14">$quantity_1c</td>
    <td class="text14">$quantity_1c_without_sell_tovar</td>   

<!-- ***************** дынные по ВБ ******************************************** -->
    <td class="text14 $wb_sell_tovar_color">$sell_count_wb</td>
    <td class="text14 $wb_css_ostatok">$quantity_wb</td>
HTML;

    
     if ($wb_check_point == 0)  {
        echo "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_wb_value_$key\" value=$quantity_new_wb></td>";
        echo "<td><input type=\"checkbox\" name=\"_wb_check_$key\" value=\"0\"></td>";
    }elseif ($wb_check_point == 999) {
        echo "<td></td>";
        echo "<td></td>";
    } else {
        echo "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_wb_value_$key\" value=$quantity_new_wb></td>";
        echo "<td><input checked type=\"checkbox\" name=\"_wb_check_$key\" value=\"1\"></td>";
    }     

   

echo <<<HTML
<!-- ***************** данные по WB IP******************************************** -->
    <td class="text14 $wbip_sell_tovar_color">$sell_count_wbip</td>
    <td class="text14 $wbip_css_ostatok">$quantity_wbip</td>
    
HTML;
    if ($wbip_check_point == 0)  {
        echo "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_wbip_value_$key\" value=$quantity_new_wbip></td>";
        echo "<td><input type=\"checkbox\" name=\"_wbip_check_$key\" value=\"0\"></td>";
    }elseif ($wbip_check_point == 999) {
        echo "<td></td>";
        echo "<td></td>";
    } else {
        echo "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_wbip_value_$key\" value=$quantity_new_wbip></td>";
        echo "<td><input checked type=\"checkbox\" name=\"_wbip_check_$key\" value=\"1\"></td>";
    } 

echo <<<HTML
<!-- ***************** данные по OZON******************************************** -->
    <td class="text14 $ozon_sell_tovar_color">$sell_count_ozon</td>
    <td class="text14 $ozon_css_ostatok">$quantity_ozon</td>
HTML;
if ($ozon_check_point == 0)  {
    echo "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_ozon_value_$key\" value=$quantity_new_ozon></td>";

    echo "<td><input type=\"checkbox\" name=\"_ozon_check_$key\" value=\"0\"></td>";
} elseif ($ozon_check_point == 999) {
    echo "<td></td>";
    echo "<td></td>";

} else {
    echo "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_ozon_value_$key\" value=$quantity_new_ozon></td>";

    echo "<td><input checked type=\"checkbox\" name=\"_ozon_check_$key\" value=\"1\"></td>";
} 

// Нераспределенный товар 
echo "<td>---</td>";
echo "<td class=\"text14\">$neraspred_ostatki_tovarov</td>";
echo "<td class=\"text14\">$min_ostatok_tovarov</td>";
echo "<td class=\"text14 $need_some_tovarov\">$tovari_k_popolneniu</td>";


echo <<<HTML
    
    <input hidden type="text"  name = "_wb_BarCode_$key" value="$wb_barCode">
    <input hidden type="text"  name = "_wbip_BarCode_$key" value="$wbip_barCode">
    <input hidden type="text"  name = "_ozon_BarCode_$key" value="$ozon_barCode">
    
</tr>
HTML;
}
echo <<<HTML
</table>

<input class="btn" type="submit" value="ОБНОВИТЬ ДАННЫЕ">
</form>
</div>


HTML;



