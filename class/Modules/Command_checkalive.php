<?php
	if(!empty($Params[1]))
	{	
			$Text = str_replace("!checkalive", "", $Text); //Cortamos el comando para quedarnos solo con la url
			$status = online($Text);
			if($status){	
				$WhatsApp->SendRawMessage($From, $status);
			}
	}
	else{
		return SEND_USAGE;
	}
?>
