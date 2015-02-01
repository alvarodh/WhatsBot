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
	}

	class Youtube extends YoutubeAPI
	{
	}