<?php

function amoCRMCurl($data = array()) {
	$curl=curl_init(); #Сохраняем дескриптор сеанса cURL

	#Устанавливаем необходимые опции для сеанса cURL
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $data['link']);

	if (isset( $data['postfields'] )) {
		curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl,CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data['postfields']));
	}
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_COOKIEFILE, __DIR__.'/cookie.txt');
	curl_setopt($curl,CURLOPT_COOKIEJAR, __DIR__.'/cookie.txt');
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 0);

	$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
	$code=curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	CheckAmoCurlResponse($code);

	return json_decode($out, true);
}


function CheckAmoCurlResponse($code)
{
	$code=(int)$code;
	$errors=array(
		301=>'Moved permanently',
		400=>'Bad request',
		401=>'Unauthorized',
		403=>'Forbidden',
		404=>'Not found',
		500=>'Internal server error',
		502=>'Bad gateway',
		503=>'Service unavailable'
	);
	try
	{
		#Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
		if($code!=200 && $code!=204)
			throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', $code);
	}
	catch(Exception $E)
	{
		die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
	}
}



?>