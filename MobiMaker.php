<?php 
/**
* 
*/
class MobiMaker
{
	private $posts;
	private $directory;
	
	function __construct($posts)
	{
		$this->posts=$posts;
		$regpattern = array("/January/",
							"/February/",
							"/March/",
							"/April/",
							"/May/",
							"/June/",
							"/July/",
							"/August/",
							"/September/",
							"/October/",
							"/November/",
							"/December/",
							"/Sunday/",
							"/Monday/",
							"/Tuesday/",
							"/Wednesday/",
							"/Thursday/",
							"/Friday/",
							"/Saturday/",
							"/ /","/\//","/,/");
		$regreplace = array("01-",
							"02-",
							"03-",
							"04-",
							"05-",
							"06-",
							"07-",
							"08-",
							"09-",
							"10-",
							"11-",
							"12-",
							"--",
							"--",
							"--",
							"--",
							"--",
							"--",
							"--"
						);
		$dirtitle = preg_replace($regpattern,$regreplace, $this->posts->title);
		$this->directory="./$dirtitle";
		// echo $dirtitle."\n";
	}

	function createbookdir(){
		mkdir($this->directory);
	}

	function createtextfiles(){
		$posts = $this->posts;
		$templatefh = fopen("EpubTemplate/OEBPS/Text/posttemplate.xhtml", "r");
		$template = fread($templatefh, filesize("EpubTemplate/OEBPS/Text/posttemplate.xhtml"));
		fclose($templatefh);
		foreach ($this->posts->posts as $key => $value) {
			
			$posttitle = $value["title"];
			$postauthor = $value["author"];
			$postbody = $value["body"];
			$regpattern = array("/posttitle/",
								"/postauthor/",
								"/postbody/",
							);
			$regreplace = array ($posttitle,
								 $postauthor,
								 $postbody
								);
			$output = preg_replace($regpattern, $regreplace, $template);
			$outfilepath = "$this->directory/$posttitle--$postauthor";
			$outfh = fopen($outfilepath, "w");
			fwrite($outfh, $output);
			fclose($outfh);
		}
	}

	function createtitlefile(){
		$templatefh=fopen("EpubTemplate/OEBPS/Text/title.xhtml","r");
		$filetext=fread($templatefh, filesize("EpubTemplate/OEBPS/Text/title.xhtml"));
		fclose($templatefh);
		$filetext = preg_replace("/collectiontitle/", $this->posts->title, $filetext);
		$outputfile = $this->directory."/title.xhtml";
		$outputfh = fopen($outputfile, "w");
		fwrite($outputfh, $filetext);
		fclose($outputfh);
	}

}


 ?>