<?php
	require_once 'Utils.php';
	require_once 'Lang.php';

	class WhatsBotCaller
	{
		private $ModuleManager = null;
		private $Whatsapp = null;

		public function __construct(&$MDLM, WhatsappBridge &$WPB)
		{
			if($MDLM != null && !($MDLM instanceof ModuleManager)) // Podríamos testear en cada método si $this->ModuleManager está o no instanciado correctamente
				trigger_error('You must pass a ModuleManager to WhatsBotCaller', E_USER_ERROR);
				
			$this->ModuleManager = &$MDLM;
			$this->Whatsapp = &$WPB;
		}

		public function CallModule($ModuleName, $Filename, $Params, $Me, $ID, $Time, $From, $Name, $Text)
		{
			$ModuleManager = &$this->ModuleManager; // add to !reload, !update, etc...
			$Whatsapp = &$this->Whatsapp;

			$Lang = new Lang($ModuleName);

			return include $Filename;
		}

		public function CallDomainModule($ModuleName, $Filename, $ParsedURL, $URL, $Me, $ID, $Time, $From, $Name, $Text)
		{
			$Whatsapp = &$this->Whatsapp;

			$Lang = new Lang($ModuleName);

			return include $Filename;
		}

		public function CallExtensionModule($ModuleName, $Filename, $Me, $From, $ID, $Type, $Time, $Name, $Text, $URL, $ParsedURL)
		{
			$Whatsapp = &$this->Whatsapp;

			$Lang = new Lang($ModuleName);

			return include $Filename;
		}

		public function CallMediaModule($ModuleName, $Filename, $Me, $From, $ID, $Type, $Time, $Name, Array $Data)
		{
			$Whatsapp = &$this->Whatsapp;

			$Lang = new Lang($ModuleName);

			extract($Data);

			return include $Filename;
		}

		public function CallParserModule($ModuleName, $Filename, $Me, $From, $ID, $Time, $Name, $Text, $Action, $Object)
		{
			$Whatsapp = &$this->Whatsapp;
			$ModuleManager = &$ModuleManager;

			$Lang = new Lang($ModuleName);

			return include $Filename;
		}
	}