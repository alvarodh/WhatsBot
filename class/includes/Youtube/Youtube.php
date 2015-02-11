<?php
	require_once 'API.php';

	class Youtube extends YoutubeAPI
	{
		public function SearchMusic($Title, $Artist = null, $MaxResults = 10)
		{
			$Query1 = strtolower("{$Artist} {$Title}");
			$Query2 = strtolower("{$Title} {$Artist}");

			$Videos = $this->SearchVideo($Query1, $MaxResults);

			if($Videos !== false)
			{
				$Videos = $Videos['items']; // Return WITH info

				$Count = count($Videos);
				for($i = 0; $i < $Count; $i++)
				{
					$Videos[$i]['title_lower'] = strtolower($Videos[$i]['title']);
					$Videos[$i]['channel']['title_lower'] = strtolower($Videos[$i]['channel']['title']);

					$Videos[$i]['match'] = 0;

					# Channel info

					if($this->_IsVerifiedChannel($Videos[$i]['channel']['id']))
						$Videos[$i]['match'] += 15;

					if(strpos($Videos[$i]['channel']['title_lower'], 'vevo') !== false) // Ends with?
						$Videos[$i]['match'] += 15;

					if(strpos($Videos[$i]['channel']['title_lower'], 'official') !== false || strpos($Videos[$i]['channel']['title_lower'], 'oficial'))
						$Videos[$i]['match'] += 10;

					# Title info

					if((strpos($Videos[$i]['title_lower'], 'cover') !== false) === (strpos($Query1, 'cover') !== false))
						$Videos[$i]['match'] += 10;
					if((strpos($Videos[$i]['title_lower'], 'remix') !== false) === (strpos($Query1, 'remix') !== false))
						$Videos[$i]['match'] += 10;
					if((strpos($Videos[$i]['title_lower'], 'mashup') !== false) === (strpos($Query1, 'mashup') !== false))
						$Videos[$i]['match'] += 10;

					if(strpos($Videos[$i]['title_lower'], 'original') !== false)
						$Videos[$i]['match'] += 10;
					if(strpos($Videos[$i]['title_lower'], 'official') !== false || strpos($Videos[$i]['title_lower'], 'oficial') !== false)
						$Videos[$i]['match'] += 10;

					// Delete non-alphnum chars
					if($Artist !== null)
					{
						# Channel info

						similar_text(strtolower($Artist), $Videos[$i]['channel']['title_lower'], $Similar);

						$Videos[$i]['match'] += (int) $Similar / 10;

						# Title info

						similar_text($Query1, $Videos[$i]['title_lower'], $S1);
						similar_text($Query2, $Videos[$i]['title_lower'], $S2);

						if($S1 > $S2)
							$Videos[$i]['similar'] = $S1;
						else
							$Videos[$i]['similar'] = $S2;
					}
					else
					{
						$Videos[$i]['similar'] = 0;

						$Words = str_word_count($Videos[$i]['title_lower'], 1);

						$Limit = count($Words);
						$Limit *= $Limit;

						for($i = 0; $i < $Limit; $i++)
						{
							$W = $Words;
							shuffle($W);

							similar_text($Query1, implode(' ', $W), $M);

							if($M > $Videos[$i]['similar'])
								$Videos[$i]['similar'] = $M;
						}
					}

					$Videos[$i]['match'] += $Videos[$i]['similar'];
				}

				usort($Videos, function($First, $Second)
				{
					if($First['match'] === $Second['match'])
						return 0;

					return ($First['match'] > $Second['match']) ? -1 : 1;
				});

				return $Videos;
			}

			return false;
		}
	}