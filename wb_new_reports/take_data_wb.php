<?php
require_once "functions/functions.php";
require_once "wb_get_sebes.php";
require_once "../../tokens/topen.php";
/// для ООО
if (isset($_GET['wb'])){
if ($_GET['wb'] == 1) {
$token_wb_stat = $token_wb_stat;
echo "<b>СТАТИСТИКА ДЛЯ ООО</b>";
}
/// для ИП
if ($_GET['wb'] == 2) {
    $token_wb_stat = $token_wb_ip_stat;
    echo "<b>СТАТИСТИКА ДЛЯ ИП</b>";
}
}
    

if (isset($_GET['dateFrom'])) {
    $dateFrom = $_GET['dateFrom'];
} else {
    $dateFrom = false;
}

if (isset($_GET['dateTo'])) {
    $dateTo = $_GET['dateTo'];
} else {
    $dateTo = false;
}


echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="" method="get">
<label>Магазин</label>
<select required name="wb">
HTML;
if (isset($_GET['wb'])){
    if ($_GET['wb'] == 1) {
echo <<<HTML
        <option selected value = "1">WB ООО</option>
        <option value = "2">WB ИП</option>
HTML;
    }
    /// для ИП
    elseif ($_GET['wb'] == 2) {
echo <<<HTML
        <option value = "1">WB ООО</option>
        <option selected value = "2">WB ИП</option>
HTML;
    } else {
echo <<<HTML
            <option value = "1">WB ООО</option>
            <option  value = "2">WB ИП</option>
HTML;    
        }

} else {
echo <<<HTML
    <option value = "1">WB ООО</option>
    <option  value = "2">WB ИП</option>
HTML;    
}


echo <<<HTML

</select>


<label>дата начала</label>
<input required type="date" name = "dateFrom" value="$dateFrom">
<label>дата окончания</label>
<input required type="date" name = "dateTo" value="$dateTo">

<input type="submit"  value="START">
</form>
HTML;


if (($dateFrom == false) or ($dateTo == false)) {
die ('Нужно выбрать даты');
} 

$dop_link = "?dateFrom=".$dateFrom."&dateTo=".$dateTo;
// $link_wb = "https://statistics-api.wildberries.ru/api/v1/supplier/reportDetailByPeriod".$dop_link;
$link_wb = 'https://statistics-api.wildberries.ru/api/v1/supplier/reportDetailByPeriod'.$dop_link;

$arr_result = light_query_without_data($token_wb_stat, $link_wb);

/*
Проверяем нет ли ошибки взаимодействия
*/
if (isset($arr_result['code'])) {
    if ($arr_result['code'] == 429) {
    echo "<br>".$arr_result['message']."<br>";
    die ('');
    }
} 

/*
Проверяем нет ли ошибки по возварту данных
*/
if (isset($arr_result['errors'][0])) {
    echo "<br>".$arr_result['errors'][0]."<br>";
    die ('WB не вернул данные');
    } 

/*
Проверяем eсть ли вообще массив 
*/
if (!isset($arr_result)) {
    echo "<br>Нет массива для вывода<br>";
    die ('WB не вернул данные');
    } 

/*

/*
Выводим наш массив 
*/    
echo "<br>************************************************************************************<br>";
// echo "<pre>";
// print_r($arr_result);
// echo "<br>************************************************************************************<br>";

require_once '../../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';



// $xls = new PHPExcel();
// $xls->setActiveSheetIndex(0);
// $sheet = $xls->getActiveSheet();

// $sheet->setCellValue("D"."1", "Сумма продаж (возвратов)");
// $sheet->setCellValue("F"."1", "Количество доставок");
// $sheet->setCellValue("G"."1", "КОммисия без НДС");
// $sheet->setCellValue("H"."1", "К перечислению продавцу за реализованный товар");
// $sheet->setCellValue("I"."1", "Возмещение за выдачу и возврат товаров на ПВЗ");
// $sheet->setCellValue("J"."1", "Возмещение издержек по эквайрингу");
// $sheet->setCellValue("K"."1", "Вознаграждение WB без НДС");
// $sheet->setCellValue("L"."1", "НДС с вознаграждения WB");
// $sheet->setCellValue("M"."1", "Доплаты");
// $sheet->setCellValue("N"."1", "Штрафы");
// $sheet->setCellValue("O"."1", "Возмещение издержек по перевозке.");
// $sheet->setCellValue("P"."1", "Стоимость логистики");




// $next_i = 2;
//     foreach ($arr_result  as $q_items) {
//          if (isset($q_items['realizationreport_id'])) {$sheet->setCellValue("A".$next_i, $q_items['realizationreport_id']);}
//          if (isset($q_items['sa_name'])) { $sheet->setCellValue("B".$next_i, $q_items['sa_name']);}
//          if (isset($q_items['supplier_oper_name'])) { $sheet->setCellValue("C".$next_i, $q_items['supplier_oper_name']);}
//          if (isset($q_items['retail_amount'])) { $sheet->setCellValue("D".$next_i, $q_items['retail_amount']);}
//          if (isset($q_items['commission_percent'])) { $sheet->setCellValue("E".$next_i, $q_items['commission_percent']);}
//          if (isset($q_items['delivery_amount'])) { $sheet->setCellValue("F".$next_i, $q_items['delivery_amount']);}
//          if (isset($q_items['ppvz_sales_commission'])) { $sheet->setCellValue("G".$next_i, $q_items['ppvz_sales_commission']);}
//          if (isset($q_items['ppvz_for_pay'])) { $sheet->setCellValue("H".$next_i, $q_items['ppvz_for_pay']);}
//          if (isset($q_items['ppvz_reward'])) { $sheet->setCellValue("I".$next_i, $q_items['ppvz_reward']);}
//          if (isset($q_items['acquiring_fee'])) { $sheet->setCellValue("J".$next_i, $q_items['acquiring_fee']);}
//          if (isset($q_items['ppvz_vw'])) { $sheet->setCellValue("K".$next_i, $q_items['ppvz_vw']);}
//          if (isset($q_items['ppvz_vw_nds'])) { $sheet->setCellValue("L".$next_i, $q_items['ppvz_vw_nds']);}
//          if (isset($q_items['additional_payment'])) { $sheet->setCellValue("M".$next_i, $q_items['additional_payment']);}

//          if (isset($q_items['penalty'])) { $sheet->setCellValue("N".$next_i, $q_items['penalty']);}
//          if (isset($q_items['rebill_logistic_cost'])) { $sheet->setCellValue("O".$next_i, $q_items['rebill_logistic_cost']);}
//          if (isset($q_items['delivery_rub'])) { $sheet->setCellValue("P".$next_i, $q_items['delivery_rub']);}

    
         
//          $next_i++; // смешение по строкам
     
//         } 
 
//      $objWriter = new PHPExcel_Writer_Excel2007($xls);
//      $num_report = $q_items['realizationreport_id'];
//      $file_name_1c_list_q = "WB_".$num_report."(".date('Y-m-d').").xlsx";
//      $objWriter->save('reports/'.$file_name_1c_list_q);  
   

// die();
/*******************************************************************************************
*   Запускаем проверки (ЕСТЬ ЛИ МАССИВ)
******************************************************************************************/
// если ВБ не ответил
if ((@$arr_result['code'] == 401)) {
    die ('<br><br>НЕТ ДАННЫХ ДЛЯ ВЫВОДА, ОТРИЦАТЕЛЬНЫЙ РЕЗУЛЬТАТ ОБМЕНА ДАННЫМИ С ВБ');
}

if (!$arr_result) {
    die ('<br><br>НЕТ ДАННЫХ ДЛЯ ВЫВОДА, ВБ ВЕРНУЛ НУЛЕВОЙ МАССИВ ДАННЫХ');
}



  // формируем массива с артикулами
  foreach ($arr_result as $item) {
    $arr_key[] = $item['sa_name']; // массив артикулов
  }
  $arr_key = array_unique($arr_key); //  оставляем только уникальные артикулы


$sum_k_pererchisleniu = 0;
$sum_logistiki = 0;
$sum_shtraf = 0 ;
$sum_voznagrazhdenie_wb = 0;
$sum_vozvratov = 0;
$sum_avance = 0 ;
$sum_brak = 0;
$sum_nasha_viplata = 0;

$prodazh=0;
$stornoprodazh=0;
$correctProdazh=0;
$guts_summa_sell=0;
echo "<pre>";
foreach ($arr_result as $item) {
    // print_r($item);
    // Сумма к перечислению************************************************************************************************************
    
    if (($item['supplier_oper_name'] == 'Продажа') ) {

        $arr_sum_k_pererchisleniu[$item['sa_name']] = @$arr_sum_k_pererchisleniu[$item['sa_name']] + $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
        $arr_count[$item['sa_name']] = @$arr_count[$item['sa_name']] + $item['quantity'];
        $prodazh++;

        ////// для Гуца ***************************
     
        $arr_count_sell[] = array ('article' =>$item['sa_name'],
                                   'quantity' =>$item['quantity'],
                                   'price' =>  $item['retail_amount']);
        $guts_summa_sell = $guts_summa_sell + $item['retail_amount'];

 
    }
// Сумма к перечислению (Корректная продажа) ********************************************************************************************
    
    elseif (($item['supplier_oper_name'] == 'Корректная продажа') ) {

        $arr_sum_k_pererchisleniu[$item['sa_name']] = @$arr_sum_k_pererchisleniu[$item['sa_name']] + $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
        $arr_count[$item['sa_name']] = @$arr_count[$item['sa_name']] + $item['quantity'];
        $correctProdazh++;
 
        ////// для Гуца ***************************
        $arr_count_sell[] = array ('article'  => $item['sa_name'],
                                   'quantity' => $item['quantity'],
                                   'price'    => $item['retail_amount']);
        $guts_summa_sell = $guts_summa_sell + $item['retail_amount'];

    }
   


// Сторно продаж ************************************************************************************************************
    elseif (($item['supplier_oper_name'] == 'Сторно продаж') ) {

        $arr_sum_k_pererchisleniu[$item['sa_name']] = @$arr_sum_k_pererchisleniu[$item['sa_name']] - $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  - $item['ppvz_for_pay'];
        $arr_count[$item['sa_name']] = @$arr_count[$item['sa_name']] - $item['quantity'];
        $stornoprodazh++;
  
        ////// для Гуца ***************************
        $arr_count_vozvrat[] = array ('article'  => $item['sa_name'],
                                      'quantity' => -$item['quantity'],
                                      'price'    => -$item['retail_amount']);  

        $guts_summa_sell = $guts_summa_sell - $item['retail_amount'];
      }
  // Сумма возвоатов ************************************************************************************************************
    elseif ($item['supplier_oper_name'] == 'Возврат') {
        $arr_sum_vozvratov[$item['sa_name']] = @$arr_sum_vozvratov[$item['sa_name']] + $item['ppvz_for_pay'];
        $sum_vozvratov = $sum_vozvratov  + $item['ppvz_for_pay'];
        $arr_count[$item['sa_name']] = @$arr_count[$item['sa_name']] - 1;

        //  print_r($item);

        ////// для Гуца ***************************
        $arr_count_vozvrat[] = array ('article'  =>  $item['sa_name'],
                                      'quantity' => -$item['quantity'], // количество с отрицательным значением
                                      'price'    => -$item['retail_amount']);
        $guts_summa_sell = $guts_summa_sell - $item['retail_amount'];
    
    }
    
    // Авансовая оплата за товар без движения
    elseif ($item['supplier_oper_name'] == 'Авансовая оплата за товар без движения') {
        $arr_sum_avance[$item['sa_name']] = @$arr_sum_avance[$item['sa_name']] + $item['ppvz_for_pay'];
        $sum_avance = $sum_avance  + $item['ppvz_for_pay'];
    }

    //Частичная компенсация брака  ИЛИ Компенсация подмененного товара
    elseif (($item['supplier_oper_name'] == 'Частичная компенсация брака') || ($item['supplier_oper_name'] == 'Компенсация подмененного товара') )  {
        $arr_sum_brak[$item['sa_name']] = @$arr_sum_brak[$item['sa_name']] + $item['ppvz_for_pay'];
        $sum_brak = $sum_brak  + $item['ppvz_for_pay'];
    }


    // Сумма логистики ************************************************************************************************************
    elseif ($item['supplier_oper_name'] == 'Логистика') {
        $arr_sum_logistik[$item['sa_name']] = @$arr_sum_logistik[$item['sa_name']] + $item['delivery_rub'];
        $sum_logistiki = $sum_logistiki  + $item['delivery_rub'];
    }
    // Сумма логистики ИПЕШНИКАМ ************************************************************************************************************
    elseif ($item['supplier_oper_name'] == 'Возмещение издержек по перевозке') {
        $arr_sum_logistik[$item['sa_name']] = @$arr_sum_logistik[$item['sa_name']] + $item['rebill_logistic_cost'];
        $sum_logistiki = $sum_logistiki  + $item['rebill_logistic_cost'];
    }
    
    elseif ($item['supplier_oper_name'] == 'Логистика сторно') {
        $arr_sum_logistik[$item['sa_name']] = @$arr_sum_logistik[$item['sa_name']] - $item['delivery_rub'];
        $sum_logistiki = $sum_logistiki  - $item['delivery_rub'];
    }
  
    // Сумма ШТРАФОв   ************************************************************************************************************
    elseif ($item['supplier_oper_name'] == 'Штрафы') {
        $arr_sum_shtraf[$item['sa_name']] = @$arr_sum_shtraf[$item['sa_name']] + $item['penalty'];
        $sum_shtraf = $sum_shtraf  + $item['penalty'];
    } else {
        $array_neuchet[] = $item;
    }
    
    // Вознаграждение ВБ  ************************************************************************************************************
   
        $arr_sum_voznagrazhdenie_wb[$item['sa_name']] = @$arr_sum_voznagrazhdenie_wb[$item['sa_name']] + $item['ppvz_vw']  + $item['ppvz_vw_nds'];
        $sum_voznagrazhdenie_wb = $sum_voznagrazhdenie_wb  + $item['ppvz_vw']  + $item['ppvz_vw_nds'];
   
    }


    /// Выводим необработанные строки из отчета
if (isset($array_neuchet)){
    echo "<pre>";
    print_r($array_neuchet);
    echo "<br>";
} else {
    echo "Все данные обработаны<br><br>";
}


    // echo "<pre>";
        
    // print_r($arr_count_vozvrat);   

    // foreach ($arr_count_sell as $item){
    //     // print_r($item);
    //     $first_param = $item['article'];
    //     $sec_param = $item['price'];
    //     echo "<br>";
    //          $new_arr_count_sell["$first_param"]["$sec_param"] = @$new_arr_count_sell["$first_param"]["$sec_param"] + $item['quantity'];

    // }
    
 echo "БЫЛО :".count($arr_count_sell)."<br>";


 // Удаляем из массива все "Продажи СТОРОННО
 //если есть возвраты, то удаляем из массива все Возвраты и Продажи сторно

if (isset($arr_count_vozvrat)) {
 echo "Возвратов :".count($arr_count_vozvrat)."<br>";
foreach ($arr_count_vozvrat as $vozvrat_item) {
    foreach ($arr_count_sell as $key => $sell_item) {
          if (($vozvrat_item['article'] == $sell_item['article']) && ($vozvrat_item['price'] == -$sell_item['price'])) {
                unset($arr_count_sell[$key]);
                break 1;
            }
       }
   }
} else {
    echo "НЕТ Возвратов <br>";  
}
echo "СТАЛО :".count($arr_count_sell)."<br>";


/// формируем массив для отчет акоммистонера
foreach ($arr_count_sell as $item){
        $first_param = $item['article'];
        $sec_param = $item['price'];
        $new_arr_count_sell["$first_param"]["$sec_param"] = @$new_arr_count_sell["$first_param"]["$sec_param"] + $item['quantity'];

    }






    // выводим массив 
if (isset($arr_count_sell)){

} else {
    echo "Все данные обработаны<br><br>";
}
// print_r ($new_arr_count_sell);


echo "<br>";
echo "summa = $guts_summa_sell";
echo "<br>Продаж: $prodazh";
echo "<br>СТОРНО продаж: $stornoprodazh";
echo "<br>Коррек Продажа: $correctProdazh<br>";



if (isset($arr_count_vozvrat) ) {
$arr_sum = array_merge($arr_count_sell, $arr_count_vozvrat);
}

// print_r($arr_sum);
/******************************************************************************
* Рисуем ттаблицу
 *****************************************************************************/


echo <<<HTML
<table class="prod_table">
  <tr>
<td>Артикул</td>
<td>Кол-во<br> продаж</td>
<td>Сумма выплат с ВБ</td>
<td>Авансовая <br>оплата</td>

<td>Компенсация<br> брака</td>
<td>Возвраты</td>
<td>Стоимость <br> логистки</td>

<td>Комиссия ВБ</td>
<td>Штрафы ВБ</td>
<td>НАША ВЫПЛАТА</td>
<td>цена за шт</td>
<td>Себест</td>
<td>Дельта</td>
<td>Прибыль<br> с артикула</td>
 </tr>


HTML;


$sebestoimos = get_sebestiomost_wb ();
 foreach ($arr_key as $key){
// Находим себестоимость товара
    foreach ($sebestoimos as $sebes_item) {
        $right_key = mb_strtolower(make_right_articl($key));
        $right_atricle = mb_strtolower($sebes_item['article']);
        // echo "$right_key  и $right_atricle"."<br>";
        if ($right_atricle ==  $right_key) {
           $sebes_str_item = $sebes_item['sebestoimost'] ;
        //    echo "**************************** $right_key  и $right_atricle"."<br>";
           break;
        } else {
            $sebes_str_item = 0;
        }
       }

     echo "<tr>";
        echo "<td>".$key."</td>";
        echo "<td>".@$arr_count[$key]."</td>";
///     Сумма выплат с ВБ до вычета 
echo "<td class=\"plus\">".number_format(@$arr_sum_k_pererchisleniu[$key],2, ',', ' ')."</td>";

// Авансовая оплата за товар без движения
echo "<td class=\"plus\">".number_format(@$arr_sum_avance[$key],2, ',', ' ')."</td>"; 


///     Сумма компенсация брака 
echo "<td class=\"plus\">".number_format(@$arr_sum_brak[$key],2, ',', ' ')."</td>"; 

///     Сумма выплат с возвратов 
echo "<td class=\"minus\">".number_format(@$arr_sum_vozvratov[$key],2, ',', ' ')."</td>";

///     Сумма ЛОгистики 
 echo "<td class=\"minus\">".number_format(@$arr_sum_logistik[$key],2, ',', ' ')."</td>";



///     Сумма Комиссии ВБ
echo "<td class=\"minus\">".number_format(@$arr_sum_voznagrazhdenie_wb[$key],2, ',', ' ')."</td>";

///     Сумма Штрафов  
echo "<td class=\"minus\">".number_format(@$arr_sum_shtraf[$key],2, ',', ' ')."</td>";


///     Сумма к выплате
$temp[$key] =  @$arr_sum_k_pererchisleniu[$key] - @$arr_sum_vozvratov[$key] + @$arr_sum_avance[$key] +  
@$arr_sum_brak[$key] - @$arr_sum_logistik[$key] - @$arr_sum_shtraf[$key];
$sum_nasha_viplata = $sum_nasha_viplata + $temp[$key];

echo "<td class=\"our_many\">".number_format(@$temp[$key],2, ',', ' ')."</td>";  
if ((isset($arr_count[$key]) && ($arr_count[$key]) <> 0)) {
$price_for_shtuka = @$temp[$key]/@$arr_count[$key];
} else {
    $price_for_shtuka = 0;
}
///     Цена за штуку
echo "<td>".number_format($price_for_shtuka,2, ',', ' ')."</td>"; // цена за штукту

///     себестоимость
echo"<td class=\"plus\">"."$sebes_str_item"."</td>"; // себестоимость

///     Разница в стоимости
if ((isset($arr_count[$key]) && ($arr_count[$key]) <> 0)) { // если количество проданного товара не равно Нулю то считаем дельту
$temp_delta = ($price_for_shtuka - $sebes_str_item);
} else {
    $temp_delta = 0;
}

echo"<td class=\"plus\">".number_format($temp_delta,2, ',', ' ')."</td>"; // дельта
$our_pribil  = $temp_delta * @$arr_count[$key];

$sum_our_pribil = @$sum_our_pribil + $our_pribil; // Наша заработок по всем артикулам

///     Заработок с артикула 
echo"<td class=\"our_many\"><b>".number_format($our_pribil,2, ',', ' ')."</b></td>"; // заработали на артикуле
  echo "</tr>";

}

echo"<tr>";
echo"<td></td>";
echo"<td></td>";
echo"<td class=\"plus\"><b>".number_format($sum_k_pererchisleniu,2, ',', ' ')."</b></td>";
echo"<td class=\"plus\"><b>".number_format($sum_avance,2, ',', ' ')."</b></td>";
echo"<td class=\"plus\"><b>".number_format($sum_brak,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_vozvratov,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_logistiki,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_voznagrazhdenie_wb,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_shtraf,2, ',', ' ')."</b></td>";
echo"<td class=\"our_many\"><b>".number_format($sum_nasha_viplata,2, ',', ' ')."</b></td>";
echo"<td></td>";
echo"<td></td>";
echo"<td></td>";
echo"<td class=\"our_many\"><b>".number_format($sum_our_pribil,2, ',', ' ')."</b></td>";
echo "</tr>";



echo "</table>";

die('РАСЧЕТ ОКОНЧЕН');








