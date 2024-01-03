<?php
	
	//-- Instagram Scraper - Version 1.0 - 20240103
	
	use app\instagramScraper;
	use Tracy\Debugger;
	
	require_once("vendor/autoload.php");
	require_once("classes/instagramScraper.php");
	
	//-- tracy basic config (optionnal)
	Debugger::enable();
	Debugger::$strictMode = TRUE;
	Debugger::$strictMode = E_ALL; // all errors
	Debugger::$dumpTheme  = 'dark';
	//-- /tracy basic config (optionnal)
	
	$account = "feliciaday"; //-- ex: feliciaday -> https://instagram.com/feliciaday
	
	try {
		$scraper = new instagramScraper($account);
		dump($scraper->getDatas(TRUE));
		
	} catch (Exception $e) {
		die("<strong style='color:red'>ERROR</strong> : " . $e->getMessage());
	}
	
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------