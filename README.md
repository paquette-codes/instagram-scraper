# instagram-scraper v1.0 (2024, php 8.0+)
 Scraper to get the basic infos from 

## What it does:
Get the webpage of an instagram account, no login required and retrieve thoses basic values and return it in an array key/pair value:
  - Current date
  - Uri of the instagram account
  - Title
  - Handler
  - Followers
  - Following
  - Nb posts

## How to use:
1. Just enter the account name in _$account_ variable and run the script.
2. The first time, it will scrap the url and return the basic infos and save a cachefile of the account page.
3. Until the cachefile of the account is present, all subsequents run will read the cachefile instead.
4. it will return a message from where the script toke the infos and an array of the key/value pair.

## Example:
    use app\instagramScraper;
	use Tracy\Debugger;

	require_once("vendor/autoload.php");
	require_once("instagramScraper.php");
	
	$account = "feliciaday"; //-- ex: feliciaday -> https://instagram.com/feliciaday
	
	try {
		$scraper = new instagramScraper($account);
		dump($scraper->getDatas(TRUE)); //-- when set to TRUE, display the debugs message/values

	} catch (Exception $e) {
		die("<strong style='color:red'>ERROR</strong> : " . $e->getMessage());
	}

getDatas() will return an array like this:

    'date' => '2024-01-03 15:38:31'
    'uri' => 'https://www.instagram.com/feliciaday/'
    'title' => 'Felicia Day'
    'handle' => '@feliciaday'
    'followers' => '2M'
    'following' => '288'
    'nbposts' => '1,572'

## Todo:
1. Flush the cache of the account page if older that x seconds.
