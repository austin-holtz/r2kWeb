#! usr/bin/php
<?php 

echo "Welcome to post2epub!\n\n";

function mainMenu(){
	$options = array("Create an ebook file",
					);
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