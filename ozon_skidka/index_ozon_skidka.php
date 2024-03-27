<?php
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
    // озон ИП зел
 echo    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];

 echo "<br>";
 echo     $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];
echo "<br>";
$ozon_dop_url = 'v1/actions/discounts-task/list';

// 76929283514432830
// 76929283514432832

// 76929083966037274
// 76929083966037280

$send_data = '{
    "status": "SEEN",
    "page": 1,
    "limit": 50
    }';

$res = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url );


echo "<pre>";


print_r($res);


// die();



$send_data = '{
    "tasks": [
        {
            "id": 76929083966037274,
            "approved_price": 618,
            "seller_comment": "OK",
            "approved_quantity_min": 1,
            "approved_quantity_max": 1
        }
    ]
}';


// $send_data =  array (
//     "tasks" => array (array(
    
//     "id" => 76929083966037274,
//     "approved_price" => 618,
//     "seller_comment" =>  "OK",
//     "approved_quantity_min" => 1,
//     "approved_quantity_max" => 1
//     ))
// )
//     ;

//     print_r($send_data);
// $send_data = json_encode($send_data);

echo $send_data;  
echo "<br>";
// echo $test;  
// echo "<br>";
$ozon_dop_url = 'actions/discounts-task/approve';
$res22 = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url );




print_r($res22);


echo "<br><br>END";