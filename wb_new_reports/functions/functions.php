<?php
// echo "Download Functions<br>";
/****************************************************************************************************************
****************************  Простой запрос на ВБ без данных **************************************
****************************************************************************************************************/
function light_query_without_data($token_wb, $link_wb){
	$ch = curl_init($link_wb);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization:' . $token_wb,
		'Content-Type:application/json'
	));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	
	$res = curl_exec($ch);
	
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
	curl_close($ch);
	
		echo     '<br> Результат обмена (SELECT without Data): '.$http_code;
		
	$res = json_decode($res, true);
	
	return $res;
	}

/****************************************************************************************************************
**************************** Простой запрос на ВБ  с данными **************************************
****************************************************************************************************************/

function light_query_with_data($token_wb, $link_wb, $data){
	$ch = curl_init($link_wb);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization:' . $token_wb,
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	
	$res = curl_exec($ch);
	
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
	curl_close($ch);
		echo     '<br>Результат обмена(SELECT with Data): '.$http_code. "<br>";

	$res = json_decode($res, true);
	// var_dump($res); // выводит результирующий массив
	return $res;

}

/****************************************************************************************************************
****************************  ОТправка PATCH на ВБ  с данными **************************************
****************************************************************************************************************/

function patch_query_with_data($token_wb, $link_wb, $data) {
$ch = curl_init($link_wb);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Authorization:' . $token_wb,
	'Content-Type:application/json'
));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
curl_close($ch);



	echo     '<br>Результат обмена (PATCH): '.$http_code;



$res = json_decode($res, true);

return $res;
}





/****************************************************************************************************************
**************************** Получаем все новые заказы **************************************
****************************************************************************************************************/

function get_all_new_zakaz ($token_wb) {
	$link_wb = 'https://suppliers-api.wildberries.ru/api/v3/orders/new';
	$res = light_query_without_data($token_wb, $link_wb);
	return $res;
}


/****************************************************************************************************************
******************  Функция готовить информацию и запускает добавление товара в поставку *****************************
****************************************************************************************************************/
function make_sborku_one_article_one_zakaz ($token_wb, $supplyId, $orderId){
    $data = array(
        'supplyId' => $supplyId,
        'orderId' => $orderId
        );
        $link_wb = 'https://suppliers-api.wildberries.ru/api/v3/supplies/'.$supplyId."/orders/".$orderId;
    
// echo "<br>$link_wb<br>"; // выводим ссылку на экран
    
    //  Запуск добавления товара в поставку - НЕВОЗВРАТНАЯ ОПЕРАЦИЯ ***********************************
    // раскоментировать при работе
        $res =  patch_query_with_data($token_wb, $link_wb, $data);

        // echo "<pre>";
        // print_r($res);
return $res;
}



/****************************************************************************************************************
************************************* Создаем поставку на сайте WB **************************************
****************************************************************************************************************/
function make_postavka ($token_wb, $name_postavka) {
$data = array('name' => $name_postavka);
 $link_wb = 'https://suppliers-api.wildberries.ru/api/v3/supplies';
 $res = light_query_with_data ($token_wb, $link_wb, $data);

 return $res; // Возвращаем номер поставки
}


/****************************************************************************************************************
************************************* Получаем заказы из одной поставки    **************************************
****************************************************************************************************************/
function get_orders_from_supply($token_wb, $supplyId) {
	$link_wb = 'https://suppliers-api.wildberries.ru/api/v3/supplies/'.$supplyId.'/orders';
	$res =  light_query_without_data($token_wb, $link_wb);
	// echo "<pre>";
    // print_r($res['orders']);
	return $res['orders']; // 
	}


/****************************************************************************************************************
************************************* Получаем стикеры по номерам заказа  **************************************
****************************************************************************************************************/

	function get_stiker_from_supply ($token_wb, $arr_orders, $N_1C_zakaz, $article,$path_stikers_orders) {
		$dop_link="?type=png&width=40&height=30";  // QUERY PARAMETERS
		$link_wb  = "https://suppliers-api.wildberries.ru/api/v3/orders/stickers".$dop_link;;


// Разбиваем массив на 100 и менее заказов
if (count($arr_orders)> 100) {
	echo "<br><br>****** ФОРМИРУЕМ МАССИВ СО СТИКЕРАМИ, ПОПАЛИ В УСЛОВИЕ БОЛЬШЕ СОТНИ ЗАКАЗОВ  *****<br><br>";	

$kolvo_soten = 1;
$j=0;
	for ($k=0; $k < count($arr_orders); $k++){

		echo "<br>K=$k---J=$j<br>";
		if ($j == 100) {
			$kolvo_soten ++;
			$j=0;
			echo "<br>**************    Добавили СОТНЮ     *********<br>";
		}

		if ($j < 100){
			$key_order_arr=$kolvo_soten-1;
			$arr_temp_orders[$key_order_arr][]=$arr_orders[$k];

		}
		$j++;
	}


echo "<br> МАССИВ РАЗБИТЫЙ ПО СОТНЯМ <br>";
echo "<pre>";
print_r($arr_temp_orders);

foreach ($arr_temp_orders as $arr_sot_orders) {
	// массив с номерами заказа
	$data = array(
		"orders"=> $arr_sot_orders
	);
	// получаем данные со стикерами 
	$arr_temp_res_stikers[] = light_query_with_data($token_wb, $link_wb, $data);
	unset($data); // 
}

echo "<br> Массив со стикерами не преобразованный РАЗБИТЫЙ ПО СОТНЯМ <br>";
echo "<pre>";
print_r($arr_temp_res_stikers);

foreach ($arr_temp_res_stikers as $arr_temp_stikers){
	foreach ($arr_temp_stikers['stickers'] as $z_items){
		$res_stikers['stickers'][]=$z_items;
	}

}

echo "<br> МАССИВ Со стикерами ДЛЯ ПДФ <br>";
echo "<pre>";
print_r($res_stikers);



} else {

	echo "<br>*************ПОПАЛИ В УСЛОВИЕ МЕНЬШЕ  СОТНИ ЗАКАЗОВ//// *******<br>";
// ************************   Если количество заказов меньше 100 штук
	// массив с номерами заказа
	 	$data = array(
			"orders"=> $arr_orders
		);

  		// получаем данные со стикерами 
		$res_stikers = light_query_with_data($token_wb, $link_wb, $data); 
}	

		// ФОРМИРУЕМ ПДФ файл
		require_once "libs/fpdf/fpdf.php";
		//create pdf object
		$pdf = new FPDF('L','mm', array(80, 106)); // задаем пдф файл размером с пнг файл
		//add new page
		$pdf->AliasNbPages();
		
		$file_num=1; // временный порядковй номер для картинки
		foreach ($res_stikers['stickers'] as $items) {
		$filedata='';

		$pdf->AddPage();
		$file = $path_stikers_orders."/"."stik".$file_num.".png"; // создаем временный файл с картинкой QR кода.
		$filedata = base64_decode($items['file']);
		file_put_contents($file, $filedata, FILE_APPEND); // добавляем данные в файл с накопительным итогом
		$pdf->image($file,0,0,'PNG');
		unlink ($file); // удалям пнг файлы, чтобы не копить их
		
		$file_num++;
				}
		// запись в пдф файл
		$article_temp = make_rigth_file_name($article); // убираем все запрещенные символы из названия файла
		$pdf_file = "№".$N_1C_zakaz."_stikers_(".$article_temp.") ".count($res_stikers['stickers'])."шт.pdf";  

		$pdf->Output($path_stikers_orders."/".$pdf_file, 'F');

		return $pdf_file; // возвращаем название ПДФ файла для формирования  архива;
		}






function make_right_articl($article) {
			// КАНТРИ Макси 
				if ($article == '8240282402-ч' ) {
					$new_article = '82402-ч';
				} else if ($article == '8240282402-к' ) {
					$new_article = '82402-к';
				} else if ($article == '8240282402-з' ) {
					$new_article = '82402-з';
			// КАНТРИ Средний 
				} else if ($article == '8240182401-ч' ) {
					$new_article = '82401-ч';
				} else if ($article == '8240182401-з' ) {
					$new_article = '82401-з';
				} else if ($article == '8240182401-к' ) {
					$new_article = '82401-к';
			// КАНТРИ Мини 
				} else if ($article == '8240082400-к' ) {
					$new_article = '82400-к';
				} else if ($article == '8240082400-з' ) {
					$new_article = '82400-з';
				} else if ($article == '8240082400-ч' ) {
					$new_article = '82400-ч';
				} else if ($article == '82552-82552-к' ) {
					$new_article = '82400-к';
				} else if ($article == '82552-82552-ч' ) {
					$new_article = '82400-ч';		
				} else if ($article == '82552-82552-ол' ) {
					$new_article = '82400-з';
		
			// Приствольные круги     
				} else if ($article == '7262-КП(Л)' ) {
					$new_article = '7262-КП(л)';
				} else if ($article == '7262-КП(У)' ) {
					$new_article = '7262-КП(у)';
			
			// Якоря 
				} else if ($article == '8910-8910-30' ) {
					$new_article = '8910-30';
				} else if ($article == '1840-301840-30' ) {
					$new_article = '1840-30';
				} else if ($article == '1940_1940-10' ) {
					$new_article = '1940-10';
			// Метровые борды
				} else if ($article == '7245-К7245-К-16' ) {
					$new_article = '7245-К-16';
				} else if ($article == '7260-К-7260-К-12' ) {
					$new_article = '7260-К-12';
				} else if ($article == '7260-К7260-К-12' ) {
					$new_article = '7260-К-12';
				} else if ($article == '7280-К7280-К-80' ) {
					$new_article = '7280-К-8';
				} else if ($article == '7280-К-7280-К-8' ) {
					$new_article = '7280-К-8';
				} 
			// Приствольные круги 
				 else if ($article == '7262-КП(Л)' ) {
					$new_article = '7262-КП';
				} else if ($article == '7262-КП(У)' ) {
					$new_article = '7262-КП';
				} else if ($article == '7262-КП(Ле)' ) {
					$new_article = '7262-КП';
		  		} 		
			// Вся неучтенка    
				
				else {
					$new_article = $article;
				}
			
				return $new_article;
}

function make_rigth_file_name($temp_file_name) {
$temp_file_name=str_replace('*','_',$temp_file_name);
$temp_file_name=str_replace('/','_',$temp_file_name);
$temp_file_name=str_replace('\'','_',$temp_file_name);
$temp_file_name=str_replace(':','_',$temp_file_name);
$temp_file_name=str_replace('?','_',$temp_file_name);
$temp_file_name=str_replace('>','_',$temp_file_name);
$temp_file_name=str_replace('<','_',$temp_file_name);
$temp_file_name=str_replace('|','_',$temp_file_name);
$right_file_name=str_replace('"','_',$temp_file_name);
return $right_file_name;
}


// Функция вывода сообщения на экран 
function output_print_comment($info_comment) {
    usleep(10000); // трата на времени на добавление на вывод данных на экран
    $stamp_date = date('Y-m-d H:i:s');
    echo "<br>$stamp_date - $info_comment";
    usleep(10000); // трата на времени на добавление на вывод данных на экран
};

// Функция создает директорию, если ее нет
function make_new_dir_z($dir, $append) {

    if (!is_dir($dir)) {
        mkdir($dir, 0777, True);
    } 

}