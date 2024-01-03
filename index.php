<?php
	
	//-- Instagram Scraper - Version 1.0 - 20240103
	
	use GuzzleHttp\Client;
	use GuzzleHttp\Exception\GuzzleException;
	use Tracy\Debugger;
	
	require_once("vendor/autoload.php");
	
	$account = "feliciaday";
	
	//-- tracy basic config (optionnal)
	Debugger::enable();
	Debugger::$strictMode = TRUE;
	Debugger::$strictMode = E_ALL; // all errors
	Debugger::$dumpTheme  = 'dark';
	//-- /tracy basic config (optionnal)
	
	try {
		$uri              = 'https://www.instagram.com/' . $account . '/';
		$accountCachePage = __DIR__ . "/cachepages/" . $account . ".html";
		
		if (!file_exists($accountCachePage)) {
			$client   = new Client(['timeout' => 5.0]);
			$response = $client->request('GET', $uri);
			
			$html = compressHtml($response->getBody()->getContents());
			$html = html_entity_decode($html);
			$save = file_put_contents($accountCachePage, $html);
			if ($save === FALSE) throw new Exception("Can't save the file named : " . $account . ".html");
			
			echo "New - Status #" . $response->getStatusCode() . "<br>";
			echo "content-type " . $response->getHeaderLine('content-type') . "<br>";
			echo "Saved cache file : " . $account . ".html<br>";
			
		} else {
			$html = file_get_contents($accountCachePage);
			if ($html === FALSE) throw new Exception("Can not read the cache file named : " . $account . ".html");
			echo "Reading from file<br>";
		}
		
		$arrayInfos = getInfos($html, $uri); //-- get the infos from the source code (title + handler + followers + following + nbposts
		
		//-- tracy version (optionnal)
		dumpe($arrayInfos);
		//-- /tracy version (optionnal)
		
	} catch (GuzzleException|Exception $e) {
		die("Error : " . $e->getMessage());
	}
	
	/**
	 * Retrieves the desired information from the given HTML string.
	 *
	 * @param string $html The HTML to extract the information from.
	 * @param string $uri The URI associated with the HTML.
	 * @return array An array containing the extracted information:
	 *         - date: The current date and time in the format "Y-m-h H:i:s".
	 *         - uri: The provided URI.
	 *         - title: The extracted title from the html string.
	 *         - handle: The extracted handle fromthe html string.
	 *         - followers: The extracted number of followers from the html string.
	 *         - following: The extracted number of accounts being followed from the html string.
	 *         - nbposts: The extracted number of posts from the html string.
	 */
	function getInfos(string $html, string $uri): array
	{
		[$_title, $_handle] = explode(" (", getStringBetween($html, '<meta property="og:title" content="', ') • Instagram photos and videos" />'));
		$_followers = getStringBetween($html, '<meta content="', ' Followers, ');
		$_following = getStringBetween($html, ' Followers, ', ' Following, ');
		$_posts     = getStringBetween($html, ' Following, ', ' Posts - ');
		
		return [
			'date'      => date("Y-m-h H:i:s"),
			'uri'       => $uri,
			'title'     => $_title,
			'handle'    => $_handle,
			'followers' => $_followers,
			'following' => $_following,
			'nbposts'   => $_posts,
		];
	}
	
	/**
	 * Compresses HTML code by removing unnecessary whitespace and comments.
	 *
	 * @param string $html The HTML code to be compressed.
	 * @return string The compressed HTML code.
	 */
	function compressHtml(string $html): string
	{
		$search  = [
			'/>[^\S ]+/s',
			'/[^\S ]+</s',
			'/(\s)+/s',
			'/<!--(.|\s)*?-->/',
		];
		$replace = [
			'>',
			'<',
			'\\1',
			'',
		];
		
		return (string)preg_replace($search, $replace, $html);
	}
	
	/**
	 * Retrieves the substring between two specified strings.
	 *
	 * @param string $str The input string from which to extract the substring.
	 * @param string $from The starting string. The substring will be extracted from the first occurrence after this string.
	 * @param string $to The ending string. The substring will end right before the first occurrence of this string.
	 * @return string The substring between $from and $to. If either $from or $to cannot be found, an empty string is returned.
	 */
	function getStringBetween(string $str, string $from, string $to): string
	{
		$sub = substr($str, strpos($str, $from) + strlen($from), strlen($str));
		return substr($sub, 0, strpos($sub, $to));
	}
	
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------