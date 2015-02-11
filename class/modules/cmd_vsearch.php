<?php
	$From = Utils::GetFrom($From);
	$Text = Utils::GetText($ModuleName, $Text);

	if($Text !== false)
	{
		$Config = Utils::GetJson('config/Youtube.json');

		if(!empty($Config['key']))
		{
			if(empty($Config['max']))
				$Config['max'] = 10;

			$Youtube = new Youtube($Config['key']);

			$CharPos = strpos($Text, ',');

			if($CharPos !== false)
				$Videos = $Youtube->SearchMusic(substr($Text, 0, $CharPos), substr($Text, $CharPos), $Config['max']);
			else
				$Videos = $Youtube->SearchMusic($Text, null, $Config['max']);

			if(!empty($Videos[0]) && $Videos[0]['match'] > 80)
				$Whatsapp->SendMessage($From, "https://youtube.com/watch?v={$Videos[0]['id']}"); // Send?
			else
				$Whatsapp->SendMessage($From, "We can't found the video...");
		}
		else
			return false;
	}
	else
		$Whatsapp->SendMessage($From, 'You must write something to search...');