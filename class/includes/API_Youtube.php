<?php
	// Dividir las clases en diferentes archivos

	Unirest\Request::verifyPeer(false);
	Unirest\Request::jsonOpts(true);

	abstract class YoutubeCore
	{
		private $Key = null;

		public function __construct($Key)
		{
			$this->Key = $Key;
		}

		protected function Request($Action, Array $Params = array(), $Json = true, $WithKey = true)
		{
			if($WithKey)
				$Params['key'] = $this->Key;

			$Params = http_build_query($Params);

			$Data = Unirest\Request::get("https://www.googleapis.com/youtube/v3/{$Action}?{$Params}");

			if($Data->code === 200)
				return $Json ? $Data->body : $Data->raw_body;

			return false;
		}
	}

	class YoutubeAPI extends YoutubeCore
	{
		public function SearchVideo($Query, $MaxResults = 10)
		{ // Implement pageToken
			$Params = array
			(
				'q' => $Query,
				'maxResults' => $MaxResults,
				'type' => 'video',
				'part' => 'snippet'
			);

			$Data = $this->Request('search', $Params);

			if(is_array($Data) && $Data['kind'] === 'youtube#searchListResponse')
			{
				$Response = array();

				$Response['total'] = $Data['pageInfo']['totalResults'];
				$Response['perpage'] = $Data['pageInfo']['resultsPerPage'];

				$Response['items'] = array();

				$Count = count($Data['items']);

				for($i = 0; $i < $Count; $i++)
				{
					if($Data['items'][$i]['kind'] === 'youtube#searchResult' && $Data['items'][$i]['id']['kind'] === 'youtube#video')
					{
						$Response['items'][$i] = array
						(
							'id' => $Data['items'][$i]['id']['videoId'],
							'title' => $Data['items'][$i]['snippet']['title'],
							'description' => $Data['items'][$i]['snippet']['description'],
							'channel' => array
							(
								'id' => $Data['items'][$i]['snippet']['channelId'],
								'title' => $Data['items'][$i]['snippet']['channelTitle']
							),
							'published' => $Data['items'][$i]['snippet']['publishedAt'],
							'thumbnails' => array
							(
								'default' => $Data['items'][$i]['snippet']['thumbnails']['default']['url'],
								'medium' => $Data['items'][$i]['snippet']['thumbnails']['medium']['url'],
								'high' => $Data['items'][$i]['snippet']['thumbnails']['high']['url'],
							)
						);
					}
				}

				return $Response;
			}

			return false;
		}


		public function _IsVerifiedChannel($ChannelID)
		{
			$Source = Unirest\Request::get("https://www.youtube.com/channel/{$ChannelID}");

			if($Source->code === 200)
				if(strpos($Source->raw_body, '<span class="qualified-channel-title ellipsized has-badge">'))
					return true;

			return false;
		}
	}

	class Youtube extends YoutubeAPI
	{
		public function SearchMusic($Title, $Artist = null, $MaxResults = 10)
		{
			$Query1 = strtolower("{$Artist} {$Title}");
			$Query2 = strtolower("{$Title} {$Artist}");

			$Videos = $this->SearchVideo($Query1, $MaxResults);

			if($Videos !== false)
			{
				$Videos = $Videos['items']; // Return WHIT info

				$Count = count($Videos);
				for($i = 0; $i < $Count; $i++)
				{
					$Videos[$i]['title_lower'] = strtolower($Videos[$i]['title']);
					$Videos[$i]['channel']['title_lower'] = strtolower($Videos[$i]['channel']['title']);

					$Videos[$i]['match'] = 0;

					# Channel info

					if($this->_IsVerifiedChannel($Videos[$i]['channel']['id']))
						$Videos[$i]['match'] += 15;

					if(strpos($Videos[$i]['title_lower'], 'vevo') !== false) // Ends with?
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
						// Shuffle

						/*
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
						*/
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