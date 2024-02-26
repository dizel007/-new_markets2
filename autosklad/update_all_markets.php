<?php
require_once '../connect_db.php';
require_once '../pdo_functions/pdo_functions.php';
require_once "functions/razbor_post_array.php"; // массиво с каталогов наших товаров
require_once "../mp_functions/ozon_api_functions.php";
require_once "../mp_functions/ozon_functions.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";

// echo '<link rel="stylesheet" href="css/main_table.css">';


// Получаем все токены
$arr_tokens = get_tokens($pdo);
// ВБ АНМАКС
$token_wb = $arr_tokens['wb_anmaks']['token'];
// ВБ ГОР
$token_wb_ip = $arr_tokens['wb_ip_goryachev']['token'];
// ОЗОН АНМКАС
$client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
$token_ozon = $arr_tokens['ozon_anmaks']['token'];
/* **************************   МАссив для обновления ВБ *********************************** */
$wb_update_items_quantity = razbor_post_massive_mp($_POST);

echo "<pre>";
print_r ($wb_update_items_quantity);

if ($wb_update_items_quantity <> "no_data") {
    foreach ($wb_update_items_quantity as $wb_item) {
        $data_wb["stocks"][] = $wb_item;
    }

    }

print_r ($data_wb);
$warehouseId = 34790;
$link_wb = 'https://suppliers-api.wildberries.ru/api/v3/stocks/'.$warehouseId;
$reeeddse = put_query_with_data($token_wb, $link_wb, $data_wb);

echo "<pre>";
print_r($reeeddse);
echo "ffff";
die('jjjjjjjjjjjjjj');
