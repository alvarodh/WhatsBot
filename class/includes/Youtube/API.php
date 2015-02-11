<?php
	require_once 'Core.php';
	
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