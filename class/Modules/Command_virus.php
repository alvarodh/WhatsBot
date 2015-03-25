<?php
	require_once('Command_virus_functions.php');
	if(!empty($Params[1]))
	{	
			$Text = str_replace("!virus", "", $Text); //Cortamos el comando para quedarnos solo con la url
			$key = "";  //Nuestra api key de virustotal
			$identificador = virustotal_scanurl($Text,$key);
			$retrieve = virustotal_geturlreport($key,$identificador);
			if($retrieve){
				$variable = print_r($retrieve, true );
				$count = substr_count($variable, 'mal');			
				//return $WhatsApp->SendMessage($From, 'message:send', $count);
				$WhatsApp->SendRawMessage($From, $count." Infecciones detectadas por el antivirus... \nEn:".$Text);
			}
			else{
				$WhatsApp->SendRawMessage($From, "Error al analizar la URL");
			}
	}
	else{
		return SEND_USAGE;
	}
?>
