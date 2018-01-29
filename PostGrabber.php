<?php
/**
* 
*/
require("PHP-OAuth2/src/OAuth2/Client.php");
require("PHP-OAuth2/src/OAuth2/GrantType/IGrantType.php");
require("PHP-OAuth2/src/OAuth2/GrantType/ClientCredentials.php");
class PostGrabber
{
	private $clientID;
	private $clientSecret;
	
	function __construct($clientID,$clientSecret)
	{
		$this->clientID=$clientID;
		$this->clientSecret=$clientSecret;
	}


	function setToken(){

		$accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';
		$clientID = $this->clientID;
		$clientSecret = $this->clientSecret;

		$client = new OAuth2\Client($clientID,$clientSecret,OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
		$client->setCurlOption(CURLOPT_USERAGENT,"post2kindle by /u/reddit2kindle");
		$userpass = $this->clientID.":".$this->clientSecret;
		$params = array("user-pass"=>$userpass);
		$response = $client->getAccessToken($accessTokenUrl, "client_credentials",$params);
		$accessTokenResult = $response["result"];
		$client->setAccessToken($accessTokenResult["access_token"]);
		$client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);

		return $client;
	}

	function getPosts($subreddit = '/r/nosleep', $sort = 'hot',$limit = '25'){
		
		$fetchaddress = 'https://oauth.reddit.com'.$subreddit.'/'.$sort;

		$client = $this->setToken();

		$reqparams = array("limit"=>$limit);

		$response = $client->fetch($fetchaddress,$reqparams);
		$posts = $response["result"]["data"]["children"];
		unset($response);
		unset($client);
		$cleanedposts = array();
		foreach ($posts as $value){
			$temparray = $value["data"];
			if (!$temparray["stickied"]){
				$searches = array("&lt;","&gt;","&amp;");
				$replace = array("<",">","&");
				$cleanedbody = str_replace($searches, $replace, $temparray["selftext_html"]);
				$post = array("title"=>$temparray["title"],"body"=>$cleanedbody,"author"=>$temparray["author"]
					);
				array_push($cleanedposts, $post);
			}
		}
		$title = $subreddit." ".$sort." ".$limit." ".date("l, F j, Y");
		return new PostCollection($title,$cleanedposts);
	}
}

/**
* 
*/
class PostCollection
{	
	public $title;
	public $posts;
	function __construct($title,$posts)
	{
		$this->title = $title;
		$this->posts = $posts;
	}
}