<?php 


/**************************************************************************************
* Функция рисует шапку таблицы
**************************************************************************************/
function write_table_shapka($link, $shop_name) {
echo <<<HTML
<div class="center_form">
<form action="$link" method="post">
<table>

<tr class="prods_table">

    <td colspan="12" >$shop_name</td>


</tr>


<tr class="prods_table">
    <!-- <td width="30">пп</td> -->
    <td width="100">артикул 1С</td>
    <td width="130">артикул <br>магазина</td>
    <td>Oстатки<br>из 1С всего</td>
    <td>Кол-во<br>продано<br>везде</td>
    <td>Oстатки<br>из 1С<br>с учетом<br> проданного </td>
    <td>Кол-во<br>продано<br>на МП</td>
    <td>только<br>ФБО</td>
    <td>Процент<br>распределния</td>
    <td>Процент<br>для<br>артикула</td>
    <td>Кол-во<br>товара для <br>магазина</td>
    <td>Кол-во<br>товара<br>факт</td>
    <td>ХХХ</td>



</tr>
HTML;
    }

/**************************************************************************************
* Функция рисует таблицу с остатками товара
**************************************************************************************/

// function write_BODY_table ($mp_catalog, $wb_catalog, $wbip_catalog, $ozon_catalog , $sklads , $arr_sell_tovari ) {
function write_BODY_table ($mp_catalog, $all_catalogs, $arr_sell_tovari, $shop_name) {

foreach ($mp_catalog as $article) {
     // получаем процент распределения товаров по каждому артикулу для каждого магазина
        $all_procents=0;
        foreach ($all_catalogs as $catalog) {
            foreach ($catalog as $item_catalog) {
                if (mb_strtolower($item_catalog['main_article']) == mb_strtolower($article['main_article'])){
                    $mp_proc_ = $item_catalog['procent_raspredelenia'];
                // если артикул в данном каталоге только для ФБО , то не распределяем на него товар
                    $item_catalog['fbo_only']? $mp_proc_ = 0: $z = 1; // 
                    break 1;
                } else {
                    $mp_proc_ = 0;
                }
        }
            $all_procents = $all_procents + $mp_proc_;
        }

        
  // если товар только для ФБО
        $article['fbo_only']? $type_color = 'alarm_color': $type_color = ''; 
        
        
            echo "<tr class=$type_color \"prods_table\">";
        // главные артикул    
            if (@$temp_article != $article['main_article']) {
                echo "<td>".$article['main_article']."</td>";
            } else {
                echo "<td>".''."</td>";
            }
           
        
        // артикул на площадке
            echo "<td>".$article['mp_article']."</td>";
        // Остаток товаров из 1С
        
            if (@$temp_article != $article['main_article']) {
                echo "<td>".$article['real_ostatok']."</td>";
            } else {
                echo "<td>".''."</td>";
            }
        
        // Продано товаров во всех магазинах
        if (@$temp_article != $article['main_article']) {
            echo "<td class=\"prods_table\">".@$arr_sell_tovari[mb_strtolower($article['main_article'])]."</td>";
        } else {
            echo "<td>".''."</td>";
        }
         // Сколько товара с учетом проданных товаров во всех магазинах
         if (@$temp_article != $article['main_article']) {
        
            $ostatki_s_prodannim = $article['real_ostatok'] - @$arr_sell_tovari[mb_strtolower($article['main_article'])];
            echo "<td class=\"green_color\">".$ostatki_s_prodannim ."</td>";
        } else {
            echo "<td class=\"green_color\">".''."</td>";
        }
        
        
        // Продано на ОЗОНЕ 
        echo "<td class=\"prods_table\">".@$article['sell_count']."</td>";
         
        
        // товар продается только по ФБО ******* ФБО **********
            if ($article['fbo_only'] == 1) {
                echo "<td class=\"prods_table\">".$article['fbo_only']."</td>";
            } else {
                echo "<td>".''."</td>";
            }
        $article['fbo_only'] == 1?$koef_prodazh_FBO = 0:$koef_prodazh_FBO = 1;
        
        // процент распределения товаров с учетом ФБО
        $mag_proc_from_all_tovar = floor($article['procent_raspredelenia']/$all_procents*100*$koef_prodazh_FBO);
        
            echo "<td>".$all_procents."/".$article['procent_raspredelenia']."/".$mag_proc_from_all_tovar."</td>";
        // Для нескольких артикулов в магазинеЮ сколько каждому артикулу товара
            echo "<td>".$article['fbs']."</td>";
        
        // ********   Количество товара для данного магазина 
        $kolvo_tovarov_dlya_magazina = floor(($ostatki_s_prodannim/100) * $mag_proc_from_all_tovar * $article['fbs'] /100)-1;
        $kolvo_tovarov_dlya_magazina <0 ? $kolvo_tovarov_dlya_magazina=0 : $z=1;
        // Фактический остаток товара в Маркете
        $status_yacheiki='';
       
        ($article['quantity'] < $kolvo_tovarov_dlya_magazina )? $status_yacheiki = 'green_color' : $z = 1;  // когда товара меньше чем на складе
        ($article['quantity'] > $kolvo_tovarov_dlya_magazina )? $status_yacheiki = 'alarm_color' : $z = 1;  // когда товара больше чем на складе 
        ($article['quantity'] <= 5 )? $status_yacheiki = 'orange_color' : $z = 1;  // когда товара меньше 5 подсвечиваем оранжевым
        ($article['quantity'] <= 0 )? $status_yacheiki = 'yellow_color' : $z = 1;  // когда товара меньше 0 подсвечиваем оранжевым

        echo "<td class = \"$status_yacheiki\">".$article['quantity']."</td>";
        
        $temp_article = $article['main_article'];
        $temp_sku = $article['sku'];
        $temp_barcode = $article['barcode'];
        
      
($kolvo_tovarov_dlya_magazina == $article['quantity'])?  $check_update = 0:  $z=1;
($kolvo_tovarov_dlya_magazina > $article['quantity'])?  $check_update = 1:  $z=1;
($kolvo_tovarov_dlya_magazina < $article['quantity'])?  $check_update = 1:  $z=1;
($article['fbo_only'] == 1) ? $check_update = 0:  $z=1; // если поставки только по ФБО то снимаем значем
echo <<<HTML
        <input hidden type="text" name="_mp_BarCode_$temp_sku" value=$temp_barcode>
        <td><input class="text-field__input future_ostatok" type="number" name="_mp_value_$temp_sku" value=$kolvo_tovarov_dlya_magazina></td>
HTML;
if ($check_update  == 1) {
 echo  "<td><input type=\"checkbox\" checked name=\"_mp_check_$temp_sku\"> </td>";
} else {
    echo  "<td><input type=\"checkbox\" name=\"_mp_check_$temp_sku\" > </td>";
}
echo  "<td>$check_update</td>";

            echo "</tr>";
                    
        }
        
        
        echo <<<HTML
        </table>
        <input hidden type="text" name="shop_name" value=$shop_name>
        <input class="btn" type="submit" value="ОБНОВИТЬ ДАННЫЕ">
        </form>
        </div>
        
        
        HTML;
}