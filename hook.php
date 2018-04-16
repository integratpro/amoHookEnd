<?php

header("Content-Type: text/html; charset=utf-8");

if($_GET['value'] == "end"){//хук вешать в воронке

// Точка входа
$root = __DIR__ . DIRECTORY_SEPARATOR;

// Подготовка данных
    require $root.'config.php';
    require $root.'functions.php';
    

// Авторизация
$curldata['link'] = 'https://'.AMO_SUBDOMAIN.'.amocrm.ru/private/api/auth.php?type=json';
$curldata['postfields'] = $amo_user;
$Response = amoCRMCurl($curldata);
	unset($curldata);


    				//подготовка
    

//Получаем все данные полей
$curldata['link'] = 'https://'.AMO_SUBDOMAIN.'.amocrm.ru/api/v2/account?with=custom_fields';
$Response = amoCRMCurl($curldata);

//получаем массив enums списка мероприятий и форматов в сделках
foreach ( $Response['_embedded']['custom_fields']['leads'] as $value => $key ) {

		switch ($value) {

    			case $id_list:

        				$arr_list = $key['enums'];      //enum списка мероприятий;
        				break;

    			case $id_format_lead:

        				$arr_flead = $key['enums'];      //enum списка форматов;
 						break;

		}
}

    unset($value);
    unset($key);

//получаем массив enums мультисписка мероприятий и списка форматов в контактах
foreach ( $Response['_embedded']['custom_fields']['contacts'] as $value => $key ) {

		switch ($value) {

    			case $id_mult:

        				$arr_mult = $key['enums'];      //enum мультисписка;
        				break;

    			case $id_format_contact:

        				$arr_fcontact = $key['enums'];      //enum списка;
 						break;

		}
}

    unset($value);
    unset($key);


    				//работа со сделкой


//запрашиваем инфу о лиде
$curldata['link'] = 'https://'.AMO_SUBDOMAIN.'.amocrm.ru/api/v2/leads?id='.$lead_id;
$Response = amoCRMCurl($curldata);
//запоминаем главного контакта в лиде
$contact_id = $Response['_embedded']['items'][0]['main_contact']['id'];


//пробегаем по кастомным полям лида
foreach( $Response['_embedded']['items'][0]['custom_fields'] as $value => $key ) {

		if ( $key['id'] == $id_list ) {

				//запоминаем мероприятие
				$field_lead = $key['values'][0]['value'];
						
		} elseif ( $key['id'] == $id_format_lead ) {

				//запоминаем формат участия
				$format = $key['values'][0]['value'];

	}
}

   unset($value);
   unset($key);
   unset($curldata);


$format = array_search($format, $arr_flead); 	//находим значение в массиве enum 
												//сопоставляем и запоминаем

$enum[] = array_search($field_lead, $arr_mult);	//находим значение в массиве enum 
												//сопоставляем и запоминаем в массив 


    				//работа с контактом


//спрашиваем поля главного контакта в лиде
$curldata['link'] = 'https://'.AMO_SUBDOMAIN.'.amocrm.ru/api/v2/contacts/?id='.$contact_id;
$Response = amoCRMCurl($curldata);

//пробегаем по кастомным полям контакта 
foreach( $Response['_embedded']['items'][0]['custom_fields'] as $value => $key ) {

	if ( $key['id'] == $id_mult ) {

			for( $i = 0; $i < count($key['values']); $i++ ) {

				//запоминаем мероприятие
				$enum[] .= (string)$key['values'][$i]['enum'];
			
			}							
	} 
}

    unset($value);
    unset($key);


	//спрашиваем все сделки контакта
	foreach ( $Response['_embedded']['items'][0]['leads']['id'] as $value ) {			//пробегаем по всем сделкам

			unset($curldata);		//чищу curl при каждом проходе
    		$curldata['link'] = 'https://'.AMO_SUBDOMAIN.'.amocrm.ru/api/v2/leads/?id='.$value;
    		$Response = amoCRMCurl($curldata); 

    				//если успешно завершена
    				if ( $Response['_embedded']['items'][0]['status_id'] == 142 ) {

    						$lead_count++;
    						$sale_sum = $sale_sum + $Response['_embedded']['items'][0]['sale'];

    				} else continue;	//иначе смотрим следующую
	}


if ( $lead_count != 0 ) {	//если у контакта есть завершенные сделки

		$check = $sale_sum / $lead_count; } else { $check = 0; }
		//находим средний чек
		//если нет сделок, чек = 0


//заливаем контакт 
$contact['update'] = array(

	array(
		'id' => $contact_id,
		'updated_at' =>  time(),
		'custom_fields'=>array(
			
			array(
				'id'=>$id_mult, 
				'values'=>$enum),
			array(
				'id'=>$id_sum, 
				'values'=>array(array( 'value' => $sale_sum))),
			array(
				'id'=>$id_check, 
				'values'=>array(array( 'value' => $check))),
			array(
				'id'=>$id_lead_count, 
				'values'=>array(array( 'value' => $lead_count))), 		
			array(
				'id'=>$id_format_contact ,
				'values'=>array(array( 'value' => $format)))
		)

	)

);


// обновление контакта
unset( $curldata );
$curldata['link'] = 'https://'.AMO_SUBDOMAIN.'.amocrm.ru/api/v2/contacts';
$curldata['postfields'] = $contact;
$Response = amoCRMCurl($curldata);

} else { exit;}

?>