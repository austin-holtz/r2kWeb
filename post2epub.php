#!/usr/bin/php
<?php 

require("PostGrabber.php");
require("FileGenerator.php");

exec("clear");
echo "Welcome to post2epub!\n\n";

mainMenu();

function mainMenu(){
	$options = array("Create a custom collection pull",
					 "Load a saved collection pull"
					);

	$selection = getInput($options);

	switch($selection){
		case 0:
			customCollection();
	}
}

function customCollectionPull(){


	$subreddit = readline("\nType the subreddit you want posts from: /r/");
	$params = array();
	$endpointPrompts = array("hot", "top", "new");
	$selectionIndex = getInput($endpointPrompts);
	$endpoint = $endpointPrompts[$selectionIndex];
	if ($selectionIndex == 1){
		$timePrompts = array("day", "week", "all");
		echo "\n\nTop posts from when?\n";
		$timeSelection = getInput($timePrompts);
		$params["t"] = $timePrompts[$timeSelection];
	}


	$limit = readline("\nHow many posts do you want? press enter for 25: ");

	if ($limit) $params["limit"]=$limit;
	else $params["limit"]=25;

	gen_epub($subreddit, $endpoint, $params);

}

function savedCollectionPull()
{
	
}

function gen_epub($subreddit, $endpoint, $params){

$grabber = new PostGrabber();
$maker = new FileGenerator($grabber->getPosts($subreddit, $endpoint, $params));

$maker->gen_epub();

}

function getInput($prompts){

	$prompt = "";

	foreach ($prompts as $key => $value) {
		$promptnum = $key+1;
		echo "$promptnum. $value\n";
	}

	$selection = readline("\nEnter your selection: ");
	return $selection-1;
}

 ?>