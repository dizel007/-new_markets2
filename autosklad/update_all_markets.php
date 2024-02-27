<?php
require_once '../connect_db.php';
require_once '../pdo_functions/pdo_functions.php';
require_once "functions/razbor_post_array.php"; // массиво с каталогов наших товаров
require_once "../mp_functions/ozon_api_functions.php";
require_once "../mp_functions/ozon_functions.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";


// Получаем все токены
$arr_tokens = get_tokens($pdo);

// НАзвание магазина, который обновляем
$shop_name = $_POST['shop_name'];

echo "<br>$shop_name<br>";
echo "<pre>";

/* **************************   МАссив для обновления ВБ *********************************** */
if ($shop_name == 'wb_anmaks') {
    // ВБ АНМАКС
$token_wb = $arr_tokens['wb_anmaks']['token'];
$wb_update_items_quantity = razbor_post_massive_mp($_POST);


print_r ($wb_update_items_quantity);
if ($wb_update_items_quantity <> "no_data") {
    foreach ($wb_update_items_quantity as $wb_item) {
        $data_wb["stocks"][] = $wb_item;
    }

    }


$warehouseId = 34790;
$link_wb = 'https://suppliers-api.wildberries.ru/api/v3/stocks/'.$warehouseId;
$res = put_query_with_data($token_wb, $link_wb, $data_wb);

}

/* **************************   МАссив WB IP oбновления *********************************** */
if ($shop_name == 'wb_ip_goryachev') {
    // ВБ ГОР
$token_wb_ip = $arr_tokens['wb_ip_goryachev']['token'];

    $wb_update_items_quantity = razbor_post_massive_mp($_POST);
    
    echo "<pre>";
    print_r ($wb_update_items_quantity);
    if ($wb_update_items_quantity <> "no_data") {
        foreach ($wb_update_items_quantity as $wb_item) {
            $data_wb["stocks"][] = $wb_item;
        }
    
        }
        
    $warehouseId = 221597;
    $link_wb = 'https://suppliers-api.wildberries.ru/api/v3/stocks/'.$warehouseId;
    $res = put_query_with_data($token_wb_ip, $link_wb, $data_wb);
    
    }
    
/* **************************   МАссив ОЗОН ООО  *********************************** */

if ($shop_name == 'ozon_anmaks') {
    // ОЗОН АНМКАС
$client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
$token_ozon = $arr_tokens['ozon_anmaks']['token'];

$ozon_update_items_quantity = razbor_post_massive_mp($_POST);
$arr_catalog =  get_catalog_tovarov_v_mp('ozon_anmaks', $pdo);

echo ("<pre>");

if ($ozon_update_items_quantity <> "no_data") {

    // добавляем к массиву артикул
    foreach ($ozon_update_items_quantity as &$item) {
    
        foreach ($arr_catalog as $prods) {
         if ($item ['sku'] == $prods['barcode']) {
            $item['article'] = $prods['mp_article'];
            $item['real_sku'] = $prods['sku'];
         }
        }
    }

    unset($item);
    
    // Формируем массив для метода ОЗОНа по обновления остатков
    foreach ($ozon_update_items_quantity as $prods) {
        $temp_data_send[] = 
            array(
                "offer_id" =>  $prods['article'],
                "product_id" =>   $prods['real_sku'], // для обновления нужен СКУ а не баркод (поэтому подставляем СКУ)
                "stock" => $prods['amount'],
               );
        }
    $send_data =  array("stocks" => $temp_data_send);
    $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;
    $ozon_dop_url = "v1/product/import/stocks";
    $result_ozon = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );
    }

    
}

/* **************************   МАссив ОЗОН ИП ЗЕЛ  *********************************** */

if ($shop_name == 'ozon_ip_zel') {
    // озон ИП зел
$client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
$token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

    $ozon_update_items_quantity = razbor_post_massive_mp($_POST);
    $arr_catalog =  get_catalog_tovarov_v_mp('ozon_ip_zel', $pdo);
    // echo ("<pre>");
    // print_r($ozon_update_items_quantity);
    // echo "<br>******************************** <br>";
    // print_r($arr_catalog);
 
    
    if ($ozon_update_items_quantity <> "no_data") {
    
        // добавляем к массиву артикул
        foreach ($ozon_update_items_quantity as &$item) {
        
            foreach ($arr_catalog as $prods) {
             if ($item ['sku'] == $prods['barcode']) {
                $item['article'] = $prods['mp_article'];
                $item['real_sku'] = $prods['sku'];
             }
            }
        }
    
        unset($item);
        
        // Формируем массив для метода ОЗОНа по обновления остатков
        foreach ($ozon_update_items_quantity as $prods) {
            $temp_data_send[] = 
                array(
                    "offer_id" =>  $prods['article'],
                    "product_id" =>   $prods['real_sku'], // для обновления нужен СКУ а не баркод (поэтому подставляем СКУ)
                    "stock" => $prods['amount'],
                   );
            }



            // print_r($temp_data_send);
 
            // die('jjj');


        $send_data =  array("stocks" => $temp_data_send);
        $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;
        $ozon_dop_url = "v1/product/import/stocks";
        $result_ozon = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url );
        }
    
        print_r($result_ozon);

        die('jjj');

    }



/* *************** возвращаемся к таблице*/
   
    header('Location: get_all_ostatki_skladov_new.php?return=777', true, 301);






echo "ffff";
die('jjjjjjjjjjjjjj');
