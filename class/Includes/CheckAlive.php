<?php
function online($url) 
{
	$url = trim($url);
	try
	{
		// Crear un gestor curl
		$ch = curl_init($url);

		// Ejecutar
		curl_exec($ch);

		// Comprobar si ocurrió un error
		if(!curl_errno($ch))
		{
		 $info = curl_getinfo($ch);
		 $return = 'Se tardó ' . $info['total_time'] . ' segundos en enviar la petición a ' . $info['url']. ' con codigo '.$info['http_code'];
		}
		else
			$return = "Offline";

		// Cerrar manipulador
		curl_close($ch);
		return $return;
	}
	catch(Exception $e)
	{
		return "Error";
	}
}
?>
