<?php
	class Youtube
	{
		public static function Search($Query, $Key, $MaxResults = 10)
		{ // Implement pageToken
			$Query = urlencode($Query);

			$Data = Utils::GetRemoteJson("https://www.googleapis.com/youtube/v3/search?key={$Key}&q={$Query}&maxResults={$MaxResults}&type=video&part=snippet");

			if($Data !== false && $Data['kind'] === 'youtube#searchListResponse')
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
	}