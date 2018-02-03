#!/usr/bin/php
<?php 

require("PostGrabber.php");
require("FileGenerator.php");

exec("clear");
echo "Welcome to post2epub!\n\n";

mainMenu();

function mainMenu(){
	$options = array("Create a custom collection of posts",
					 "Load a saved collection"
					);

	$selection = getInput($options);

	switch($selection){
		case 1:
			customCollection();
	}
}

function customCollection(){
	$subreddit = readline("\nType the subreddit you want posts from: /r/");

	$endpointPrompts = array("hot", "top", "new");
	$selectionIndex = getInput($endpointPrompts)-1;
	$endpoint = $endpointPrompts[$selectionIndex];

	$limit = readline("\nHow many posts do you want? press enter for 25: ");

	gen_epub($subreddit, $endpoint, $limit);
}

function gen_epub($subreddit, $endpoint, $limit, $time=null){

$grabber = new PostGrabber();
$maker = new FileGenerator($grabber->getPosts($subreddit, $endpoint, $limit, $time));

$maker->gen_epub();

}

function getInput($prompts){

	$prompt = "";

	foreach ($prompts as $key => $value) {
		$promptnum = $key+1;
		echo "$promptnum. $value\n";
	}

	$selection = readline("\nEnter your selection: ");
	return $selection;
}

 ?>