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

			$Data = Youtube::Search($Text, $Config['key'], $Config['max']);

			if($Data !== false)
			{
				$FoundedVideo = null;

				foreach($Data['items'] as $Video)
				{
					if(strpos(strtolower($Video['channel']['title']), 'vevo') !== false)
					{
						// Si sabemos el artista y el titulo por separado, probar distintas combinaciones con similar_text
						// {titulo} {artista} - {artista} {titulo}
						// Asi evitamos tanto testeo con shuffle

						$Max = 0;

						$Words = str_word_count($Text, 1);

						$Limit = count($Words);
						$Limit *= $Limit;

						for($i = 0; $i < $Limit; $i++)
						{
							$W = $Words;
							shuffle($W);

							similar_text(implode(' ', $Words), strtolower($Text), $M);

							if($M > $Max)
								$Max = $M;
						}

						if($Max >= 70)
						{
							$FoundedVideo = $Video;
							break;
						}
					}
				}

				if($FoundedVideo !== null)
					$Whatsapp->SendMessage($From, "https://youtube.com/watch?v={$FoundedVideo['id']}");
				else
					$Whatsapp->SendMessage($From, "No encontramos el video...");
			}
		}
		else
			return false;
	}
	else
		$Whatsapp->SendMessage($From, 'You must write something to search...');