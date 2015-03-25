<?php
	// Author: Kenny Lyons aka ih8censorship
	// Website: http://pasture.sourceforge.net
	//Editado por lipman
	//Adaptación WhatsApi By Blackdrake (black-drake@hotmail.es)

function virustotal_scanurl($text, $key){
//Recibimos nuestra key y nuestra url
	try
	{
		//URL a la que enviaremos los datos
		$url = 'http://www.virustotal.com/api/scan_url.json';
		//Preparamos los campos de lo que vamos a enviar
		$fields = array('url'=>$text, 'key'=>$key);
		$fields_string='';
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		$fields_string=rtrim($fields_string,'&');
	 
		//Procedemos a enviarlo mediante cURL
		$ch = curl_init();
	 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 
		$result = curl_exec($ch);
	 
		curl_close($ch);
		//Tras enviarlo, cerramos la conexión y decodeamos el resultado devuelto
		$result = json_decode($result, true);
	 
	 
		if($result['result']=="1"){ //Si todo va bien, devolvemos la ID del scan
			return $result['scan_id'];
		} else {
			return false;
		}
	}
	catch(Exception $Exception)
	{
		Std::Out("[WARNING] [Extension::{$Extension}] Exception: " . $Exception->getMessage());
		return INTERNAL_ERROR;
	}
}
		 
function virustotal_geturlreport($key,$resource,$scan=false){
	try{
		// Esta funcion nos devuelve el reporte
		//URL a donde enviaremos los datos
		$url = 'http://www.virustotal.com/api/get_url_report.json';
		//Preparamos los campos
		$fields = array('resource'=>$resource, 'key'=>$key,'scan'=>$scan);
		$fields_string='';
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		$fields_string=rtrim($fields_string,'&');
		//Finalmente, usamos cURL para enviarlo y recoger el resultado
		$ch = curl_init();
	 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	 
		$result = curl_exec($ch);
	 
		curl_close($ch);
	 
		$result = json_decode($result, true);
	 
		 if($scan && $result['result']=="0"){
			return $result['scan_id'];
		 } else {
			return $result;
		 }
	}
	catch(Exception $Exception)
	{
		Std::Out("[WARNING] [Extension::{$Extension}] Exception: " . $Exception->getMessage());

		return INTERNAL_ERROR;
	}
}
?>
