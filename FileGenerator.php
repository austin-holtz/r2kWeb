<?php 
/**
* Look for this comment in cleaner
*/
class FileGenerator
{
	private $collection;
	private $directory;
	
	function __construct($collection)
	{
		$this->collection=$collection;
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
		$dirtitle = preg_replace($regpattern,$regreplace, $this->collection->title);
		$dirtitle = substr_replace($dirtitle, "-", -4,0);
		$this->directory="$dirtitle";
		// echo $dirtitle."\n";
	}

	function createbookdir(){
		mkdir($this->directory);
		mkdir("$this->directory/META-INF");
		mkdir("$this->directory/OEBPS");
		mkdir("$this->directory/OEBPS/Images");
		mkdir("$this->directory/OEBPS/Styles");
		mkdir("$this->directory/OEBPS/Text");
		copy("EpubTemplate/META-INF/container.xml","$this->directory/META-INF/container.xml");
		copy("EpubTemplate/OEBPS/Styles/stylesheet.css","$this->directory/OEBPS/Styles/stylesheet.css");
		copy("EpubTemplate/OEBPS/Text/backcover.xhtml", "$this->directory/OEBPS/Text/backcover.xhtml");
		copy("EpubTemplate/OEBPS/Text/bookcover.xhtml", "$this->directory/OEBPS/Text/bookcover.xhtml");
		copy("EpubTemplate/OEBPS/Images/backcover.jpg","$this->directory/OEBPS/Images/backcover.jpg");
		copy("EpubTemplate/OEBPS/Images/cover.jpg","$this->directory/OEBPS/Images/cover.jpg");
		copy("EpubTemplate/mimetype","$this->directory/mimetype");
	}

	function gen_text_files(){
		$collection = $this->collection;
		$templatefh = fopen("EpubTemplate/OEBPS/Text/posttemplate.xhtml", "r");
		$template = fread($templatefh, filesize("EpubTemplate/OEBPS/Text/posttemplate.xhtml"));
		fclose($templatefh);

		$output = array();
		foreach ($this->collection->posts as $key => $value) {
			
			$posttitle = $value["title"];
			$postauthor = $value["author"];
			$postbody = $value["body"];
			$strpattern = array("posttitle",
								"postauthor",
								"postbody",
							);
			$strreplace = array ($posttitle,
								 $postauthor,
								 $postbody
							);
			$outstr = str_replace($strpattern, $strreplace, $template);
			$outfilepath = "OEBPS/Text/$posttitle--$postauthor.xhtml";
			
			$output[$outfilepath]=$outstr;
		}

		return $output;
	}

	function gen_title_file(){
		$templatefh=fopen("EpubTemplate/OEBPS/Text/title.xhtml","r");
		$filetext=fread($templatefh, filesize("EpubTemplate/OEBPS/Text/title.xhtml"));
		fclose($templatefh);
		$filetext = str_replace("collectiontitle", $this->collection->title, $filetext);
		return $filetext;

		// $outputfile = $this->directory."/OEBPS/Text/title.xhtml";
		// $outputfh = fopen($outputfile, "w");
		// fwrite($outputfh, $filetext);
		// fclose($outputfh);
	}

	function gen_table_contents(){
		
		$posts = $this->collection->posts;

		$postHtmlTemplate = '<a href="../Text/postlink.xhtml">posttitleauthor</a><br/><br/>';
		$postOpfTemplate = ' <item id="posttitleauthor" href="Text/postlink.xhtml" media-type="application/xhtml+xml" />';

		$opfSpineTemplate = '<itemref idref="posttitleauthor" />';

		$postNcxTemplate = '
		<navPoint class="chapter" id="postlink" playOrder="playordernum">
			<navLabel>
				<text>posttitleauthor</text>
			</navLabel>
			<content src="Text/postlink.xhtml"/>
		</navPoint>';

		$htmllinks = "";
		$opflinks = "";
		$opfspine = "";
		$ncxlinks = "";

		$searches = array("postlink","posttitleauthor","playordernum");
		$count = 3;
		foreach ($posts as $value){
			$posttitle = $value["title"];
			$postauthor = $value["author"];
			$replace = array("$posttitle--$postauthor","$posttitle by $postauthor","$count");

			$posthtml = str_replace($searches, $replace, $postHtmlTemplate);
			$htmllinks.="$posthtml\n\n";

			$postopf = str_replace($searches, $replace, $postOpfTemplate);
			$postopfspine = str_replace($searches, $replace, $opfSpineTemplate);
			$opflinks.="\t\t$postopf\n";
			$opfspine .= "\t\t$postopfspine\n";

			$postncx = str_replace($searches, $replace, $postNcxTemplate);
			$ncxlinks.="\t\t$postncx\n\n";
			$count++;
		}


		/*
		*	HTML TOC
		*/

		$tocFileTemplate = fopen("EpubTemplate/OEBPS/Text/toc.xhtml","r");
		$tocTemplateText = fread($tocFileTemplate, filesize("EpubTemplate/OEBPS/Text/toc.xhtml"));
		fclose($tocFileTemplate);

		$searches = array("collectiontitle","postlinks");
		$replace = array($this->collection->title,$htmllinks);

		
		$htmlfilename = "OEBPS/Text/toc.xhtml";
		$htmloutput = str_replace($searches, $replace, $tocTemplateText);

		// $outputfile = $this->directory."/OEBPS/Text/toc.xhtml";
		// $outputfh = fopen($outputfile, "w");
		// fwrite($outputfh, $output);
		// fclose($outputfh);




		// opf file

		$opfFileTemplate = fopen("EpubTemplate/OEBPS/content.opf","r");
		$opfTemplateText = fread($opfFileTemplate, filesize("EpubTemplate/OEBPS/content.opf"));
		fclose($opfFileTemplate);

		$searches = array("postlinks","postspine","collectiontitle");
		$replace = array($opflinks,$opfspine,$this->collection->title);

		$opfoutput = str_replace($searches, $replace, $opfTemplateText);

		$opffilename = "OEBPS/content.opf";
		// $outputfh = fopen($outputfile, "w");
		// fwrite($outputfh, $output);
		// fclose($outputfh);


		// ncx file
		$tocFileTemplate = fopen("EpubTemplate/OEBPS/toc.ncx","r");
		$tocTemplateText = fread($tocFileTemplate, filesize("EpubTemplate/OEBPS/toc.ncx"));
		fclose($tocFileTemplate);

		$ncxoutput = str_replace("postlinks", $ncxlinks, $tocTemplateText);

		$ncxfilename = "/OEBPS/toc.ncx";
		
		$output = array($htmlfilename=>$htmloutput,
					 $opffilename=>$opfoutput,
					 $ncxfilename=>$ncxoutput
				);

		// print_r($output);

		return $output;

		// $outputfh = fopen($outputfile, "w");
		// fwrite($outputfh, $output);
		// fclose($outputfh);


	}

	function gen_epub(){

		$pathToEpub = "$this->directory.epub";

		$zip = new ZipArchive();
		$zip->open($pathToEpub, ZipArchive::CREATE);
		$zip->addfile("EpubTemplate/mimetype","mimetype");

		$zip->close();
		$zip->open("$this->directory.epub");

		$dirs = array("OEBPS",
					  "OEBPS/Images",
					  "OEBPS/Styles",
					  "OEBPS/Text",
					  "META-INF"
					);
		

		foreach ($dirs as $value) {
			$zip->addEmptyDir($value);
		}

		//adds text posts to zip
		$posts = $this->gen_text_files();
		foreach ($posts as $key => $value) {
			$zip->addFromString($key,$value);
		}

		//add title.xhtml file
		$titlefiletext = $this->gen_title_file();
		$zip->addFromString("OEBPS/Text/title.xhtml",$titlefiletext);

		//add content.opf, toc.xhtml, toc.ncx
		$tocfiles = $this->gen_table_contents();
		foreach ($tocfiles as $key => $value) {
			$zip->addFromString($key,$value);
		}

		//
		
		$zip->addfile("EpubTemplate/META-INF/container.xml","META-INF/container.xml");
		$zip->addfile("EpubTemplate/OEBPS/Images/cover.jpg","OEBPS/Images/cover.jpg");
		$zip->addfile("EpubTemplate/OEBPS/Text/bookcover.xhtml","OEBPS/Text/bookcover.xhtml");
		$zip->addfile("EpubTemplate/OEBPS/Images/backcover.jpg","OEBPS/Images/backcover.jpg");
		$zip->addfile("EpubTemplate/OEBPS/Text/backcover.xhtml","OEBPS/Text/backcover.xhtml");
		$zip->close();

		return $pathToEpub;

	}

	

}


 ?>