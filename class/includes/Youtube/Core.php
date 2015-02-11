<?php
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