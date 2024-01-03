<?php
	
	namespace app;
	
	use Exception;
	use GuzzleHttp\Client;
	use GuzzleHttp\Exception\GuzzleException;
	
	class instagramScraper
	{
		private string $account;
		private string $uri;
		private string $accountCachePage;
		private Client $client;
		
		/**
		 * Constructor method for creating a new instance of the class.
		 *
		 * @param string $account The Instagram account username.
		 * @return void
		 * @throws Exception
		 */
		public function __construct(string $account)
		{
			if (empty($account)) throw new Exception("Account should not be empty.");
			
			$this->account          = $account;
			$this->uri              = 'https://www.instagram.com/' . $account . '/';
			$this->accountCachePage = __DIR__ . "/cachepages/" . $this->account . ".html";
			$this->client           = new Client(['timeout' => 5.0]);
		}
		
		/**
		 * Retrieve account information from HTML and return as an array.
		 *
		 * @param bool $displayDebug (Optional) Whether to display debug information. Default is FALSE.
		 * @return array An array containing the following account information:
		 *     - date: The current date and time in "Y-m-d H:i:s" format.
		 *     - uri: The URI of the document.
		 *     - title: The extracted title from the HTML string.
		 *     - handle: The extracted handle from the HTML string.
		 *     - followers: The extracted number of followers from the HTML string.
		 *     - following: The extracted number of following from the HTML string.
		 *     - nbposts: The extracted number of posts from the HTML string.
		 * @throws Exception if an error occurs during the retrieval process.
		 */
		public function getDatas(bool $displayDebug = FALSE): array
		{
			try {
				$debugs = [];
				
				if (!file_exists($this->accountCachePage)) {
					$response = $this->client->request('GET', $this->uri);
					
					$html = $this->compressHtml($response->getBody()->getContents());
					$html = html_entity_decode($html);
					$save = file_put_contents($this->accountCachePage, $html);
					if ($save === FALSE) throw new Exception("Can't save the file named : " . $this->account . ".html");
					
					if ($displayDebug) {
						$debugs[] = "New - Status #" . $response->getStatusCode();
						$debugs[] = "content-type " . $response->getHeaderLine('content-type');
						$debugs[] = "Saved cache file : " . $this->account . ".html";
					}
					
				} else {
					$html = file_get_contents($this->accountCachePage);
					if ($html === FALSE) throw new Exception("Can not read the cache file named : " . $this->account . ".html");
					$debugs[] = "Cache - Reading from file";
				}
				
				$accountInfos = $this->getInfos($html, $this->uri);
				
				if ($displayDebug) {
					dump(['debug' => [$debugs, $accountInfos]]); //-- replace with var_dump() if you don't want to use Tracy library.
				}
				
				unset($debugs);
				return $accountInfos;
				
			} catch (GuzzleException|Exception $e) {
				throw new Exception("Error : " . $e->getMessage());
			}
		}
		
		/**
		 * Compress the HTML document by removing unnecessary whitespace and comments.
		 *
		 * @param string $html The HTML document string to compress.
		 * @return string The compressed HTML document string.
		 */
		private function compressHtml(string $html): string
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
		 * Retrieve information from HTML string and return as an array.
		 *
		 * @param string $html The HTML document string to parse.
		 * @param string $uri The URI of the document.
		 * @return array An array containing the following information:
		 *     - date: The current date and time in "Y-m-d H:i:s" format.
		 *     - uri: The URI provided as parameter.
		 *     - title: The extracted title from the HTML string.
		 *     - handle: The extracted handle from the HTML string.
		 *     - followers: The extracted number of followers from the HTML string.
		 *     - following: The extracted number of following from the HTML string.
		 *     - nbposts: The extracted number of posts from the HTML string.
		 */
		private function getInfos(string $html, string $uri): array
		{
			[$_title, $_handle] = explode(" (", $this->getStringBetween($html, '<meta property="og:title" content="', ') • Instagram photos and videos" />'));
			$_followers = $this->getStringBetween($html, '<meta content="', ' Followers, ');
			$_following = $this->getStringBetween($html, ' Followers, ', ' Following, ');
			$_posts     = $this->getStringBetween($html, ' Following, ', ' Posts - ');
			
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
		 * Retrieves the substring between two specified strings.
		 *
		 * @param string $str The input string from which to extract the substring.
		 * @param string $from The starting string. The substring will be extracted from the first occurrence after this string.
		 * @param string $to The ending string. The substring will end right before the first occurrence of this string.
		 * @return string The substring between $from and $to. If either $from or $to cannot be found, an empty string is returned.
		 */
		private function getStringBetween(string $str, string $from, string $to): string
		{
			$sub = substr($str, strpos($str, $from) + strlen($from), strlen($str));
			return substr($sub, 0, strpos($sub, $to));
		}
	}
	
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------
	//-- ---------------------------------------------------------------------------------------------------------------