<?php 

/**
* 
*/
class RedditAPI
{
	
	function __construct()
	{

	}

	function apiCall($subreddit,$endpoint,$limit){
		$token = $this->getAccessToken();
		$reqUrl = "https://oauth.reddit.com$subreddit/$endpoint";
		echo "\n\n$reqUrl\n\n";
		$ch = curl_init($reqUrl);

		$httpheader = array("Authorization: bearer $token");

		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);

		curl_setopt($ch, CURLOPT_HTTPGET, true);

		curl_setopt($ch,CURLOPT_USERAGENT,"post2epub by /u/reddit2kindle");

		// $params = array("limit"=>$limit);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $params);


		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		// print_r($response);
		$response = json_decode($response,true);
		print_r($response);
		return $response;

	}

	function getAccessToken()
	{
		$tokenURL = "https://www.reddit.com/api/v1/access_token";

		$tok = curl_init($tokenURL);

		$device_id = "DO_NOT_TRACK_THIS_DEVICE";
		$clientID = "clFuT1pNVU1MbzdhOVE6";
		$head = "Basic ". "$clientID";

		$httpheader = array("Content-Type: application/x-www-form-urlencoded",
							"Authorization: Basic clFuT1pNVU1MbzdhOVE6"
							);


		curl_setopt($tok, CURLOPT_POST, true);
		// curl_setopt($tok, CURLOPT_USERNAME, $clientID);
		curl_setopt($tok, CURLOPT_HTTPHEADER, $httpheader);
		curl_setopt($tok, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($tok, CURLINFO_HEADER_OUT, true);


		curl_setopt($tok, CURLOPT_POSTFIELDS, "grant_type=https://oauth.reddit.com/grants/installed_client&amp;device_id=DO_NOT_TRACK_THIS_DEVICE");

		$response = curl_exec($tok);
		$response = json_decode($response);
		
		return $response->access_token;
	}
}

?>