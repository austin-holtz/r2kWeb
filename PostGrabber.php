<?php
/**
* 
*/
require("RedditAPI.php");

class PostGrabber
{
	
	
	function __construct()
	{
		
	}

	function getPosts($subreddit = 'nosleep', $endpoint = 'hot',$limit = null){

		if(!strcmp($endpoint, "top")) $endpoint = "sort/$endpoint";
		
		
		$api = new RedditAPI();
		$response = $api->apiCall($subreddit,$endpoint,$limit);
		$posts = $response["data"]["children"];
		// print_r($posts);
		unset($response);
		unset($api);

		$cleanedposts = array();
		foreach ($posts as $value){
			$temparray = $value["data"];
			if (!$temparray["stickied"]&&strcmp($temparray["link_flair_text"],"Series")){
				$searches = array("&lt;","&gt;","&amp;");
				$replace = array("<",">","&");
				$cleanedbody = str_replace($searches, $replace, $temparray["selftext_html"]);
				$post = array("title"=>$temparray["title"],"body"=>$cleanedbody,"author"=>$temparray["author"]
					);
				array_push($cleanedposts, $post);
			}
		}

		$title = "/r/".$subreddit." ".$endpoint." ".$limit." ".date("l, F j, Y");
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