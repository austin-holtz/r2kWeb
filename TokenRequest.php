<?php
if (isset($_GET["error"]))
{
    echo("<pre>OAuth Error: " . $_GET["error"]."\n");
    echo('<a href="index.php">Retry</a></pre>');
    die;
}


$accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';
$clientId = 'bC5wDWg_-w4v7g';
$clientSecret = 'KQIAWonVBlYU_O-5zF4YaeqCg_I';


$redirectUrl = "http://localhost/r2kWeb/oath.php";

require("PHP-OAuth2/src/OAuth2/Client.php");
require("PHP-OAuth2/src/OAuth2/GrantType/IGrantType.php");
require("PHP-OAuth2/src/OAuth2/GrantType/ClientCredentials.php");

$client = new OAuth2\Client($clientId, $clientSecret, OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
$client->setCurlOption(CURLOPT_USERAGENT,$userAgent);

// if (!isset($_GET["code"]))
// {
//     $authUrl = $client->getAuthenticationUrl($authorizeUrl, $redirectUrl, array("scope" => "read", "state" => "SomeUnguessableValue","grant_type"=>"client_credentials","user-pass"=>"bC5wDWg_-w4v7g:KQIAWonVBlYU_O-5zF4YaeqCg_I"));
//     header("Location: ".$authUrl);
//     die("Redirect");
// }

$params = array("user-pass"=>"bC5wDWg_-w4v7g:KQIAWonVBlYU_O-5zF4YaeqCg_I");
$response = $client->getAccessToken($accessTokenUrl, "client_credentials", $params);

$accessTokenResult = $response["result"];
$client->setAccessToken($accessTokenResult["access_token"]);
$client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);

$reqparams = array("limit"=>"1");

$response = $client->fetch("https://oauth.reddit.com/r/nosleep/hot",$reqparams);

echo('<strong>Response for fetch me.json:</strong><pre>');
print_r($response);
echo('</pre>');

?>