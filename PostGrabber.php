<?php
/**
* 
*/
require("PHP-OAuth2/src/OAuth2/Client.php");
require("PHP-OAuth2/src/OAuth2/GrantType/IGrantType.php");
require("PHP-OAuth2/src/OAuth2/GrantType/ClientCredentials.php");
class PostGrabber
{
	
	function __construct()
	{

	}


	function setToken(){

		$accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';
		$clientId = 'bC5wDWg_-w4v7g';
		$clientSecret = 'KQIAWonVBlYU_O-5zF4YaeqCg_I';

		$client = new OAuth2\Client($clientId,$clientSecret,OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
		$client->setCurlOption(CURLOPT_USERAGENT,"post2kindle by /u/reddit2kindle");
		$params = array("user-pass"=>"bC5wDWg_-w4v7g:KQIAWonVBlYU_O-5zF4YaeqCg_I");
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

		
		echo("fetchaddress: ".$fetchaddress);
		print_r($response);
		echo('</pre>');
	}
}