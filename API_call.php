<?php 






$c = curl_init("https://www.reddit.com/r/nosleep/hot/");
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);


$response = curl_exec($tok);
// $response = json_decode($response);
// print_r(curl_getinfo($tok));
// echo $response;
if (!$response) echo "failed!\n";
else
print_r(json_decode($response));


?>