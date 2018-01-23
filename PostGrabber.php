<?php
/**
* 
*/
require("PHP-OAuth2/src/OAuth2/Client.php");
require("PHP-OAuth2/src/OAuth2/GrantType/IGrantType.php");
require("PHP-OAuth2/src/OAuth2/GrantType/ClientCredentials.php");
class PostGrabber
{
	private $client;
	private $accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';
	private $clientId = 'bC5wDWg_-w4v7g';
	private $clientSecret = 'KQIAWonVBlYU_O-5zF4YaeqCg_I';
		

	function __construct()
	{
		$this->client = new OAuth2\Client($this->clientId,$this->clientSecret,OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
		$this->client->setCurlOption(CURLOPT_USERAGENT,"post2kindle by /u/redditekindle");

	}


	function setToken(){
		$params = array("user-pass"=>"bC5wDWg_-w4v7g:KQIAWonVBlYU_O-5zF4YaeqCg_I");
		$response = $this->client->getAccessToken($this->accessTokenUrl, "client_credentials",$params);
		$accessTokenResult = $response["result"];
		$this->client->setAccessToken($accessTokenResult["access_token"]);
		$this->client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
	}

	function getPosts(){
		
		$this->setToken();
		

		$reqparams = array("limit"=>"1");

		$response = $this->client->fetch("https://oauth.reddit.com/r/nosleep/hot",$reqparams);

		echo('<strong>Response for fetch me.json:</strong><pre>');
		print_r($response);
		echo('</pre>');
	}
}