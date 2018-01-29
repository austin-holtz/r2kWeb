<?php 
/**
* 
*/
class MobiMaker
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
		$this->directory="./$dirtitle";
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
			$output = str_replace($strpattern, $strreplace, $template);
			$outfilepath = "$this->directory/OEBPS/Text/$posttitle--$postauthor.xhtml";
			$outfh = fopen($outfilepath, "w");
			fwrite($outfh, $output);
			fclose($outfh);
		}
	}

	function gen_title_file(){
		$templatefh=fopen("EpubTemplate/OEBPS/Text/title.xhtml","r");
		$filetext=fread($templatefh, filesize("EpubTemplate/OEBPS/Text/title.xhtml"));
		fclose($templatefh);
		$filetext = str_replace("collectiontitle", $this->collection->title, $filetext);
		$outputfile = $this->directory."/OEBPS/Text/title.xhtml";
		$outputfh = fopen($outputfile, "w");
		fwrite($outputfh, $filetext);
		fclose($outputfh);
	}

	function gen_table_contents(){
		
		$posts = $this->collection->posts;

		$postHtmlTemplate = '<a href="../Text/postlink.xhtml">posttitleauthor</a><br/><br/>';
		$postOpfTemplate = ' <item id="posttitleauthor" href="Text/postlink.xhtml" media-type="application/xhtml+xml" />';

		$opfSpineTemplate = '<itemref idref="posttitleauthor" />';

		$postNcxTemplate = '
		<navPoint class="chapter" id="postlink" playOrder="6">
			<navLabel>
				<text>posttitleauthor</text>
			</navLabel>
			<content src="Text/postlink.xhtml"/>
		</navPoint>';

		$htmllinks = "";
		$opflinks = "";
		$opfspine = "";
		$ncxlinks = "";

		$searches = array("postlink","posttitleauthor","collectiontitle");
		foreach ($posts as $value){
			$posttitle = $value["title"];
			$postauthor = $value["author"];
			$replace = array("$posttitle--$postauthor","$posttitle by $postauthor");

			$posthtml = str_replace($searches, $replace, $postHtmlTemplate);
			$htmllinks.="$posthtml\n\n";

			$postopf = str_replace($searches, $replace, $postOpfTemplate);
			$postopfspine = str_replace($searches, $replace, $opfSpineTemplate);
			$opflinks.="\t\t$postopf\n";
			$opfspine .= "\t\t$postopfspine\n";

			$postncx = str_replace($searches, $replace, $postNcxTemplate);
			$ncxlinks.="\t\t$postncx\n\n";
		}


		/*
		*	HTML TOC
		*/

		$tocFileTemplate = fopen("EpubTemplate/OEBPS/Text/toc.xhtml","r");
		$tocTemplateText = fread($tocFileTemplate, filesize("EpubTemplate/OEBPS/Text/toc.xhtml"));
		fclose($tocFileTemplate);

		$searches = array("collectiontitle","postlinks");
		$replace = array($this->collection->title,$htmllinks);

		$output = str_replace($searches, $replace, $tocTemplateText);

		$outputfile = $this->directory."/OEBPS/Text/toc.xhtml";
		$outputfh = fopen($outputfile, "w");
		fwrite($outputfh, $output);
		fclose($outputfh);




		// opf file

		$opfFileTemplate = fopen("EpubTemplate/OEBPS/content.opf","r");
		$opfTemplateText = fread($opfFileTemplate, filesize("EpubTemplate/OEBPS/content.opf"));
		fclose($opfFileTemplate);

		$searches = array("postlinks","postspine","collectiontitle");
		$replace = array($opflinks,$opfspine,$this->collection->title);

		$output = str_replace($searches, $replace, $opfTemplateText);

		$outputfile = $this->directory."/OEBPS/content.opf";
		$outputfh = fopen($outputfile, "w");
		fwrite($outputfh, $output);
		fclose($outputfh);


		// ncx file
		$tocFileTemplate = fopen("EpubTemplate/OEBPS/toc.ncx","r");
		$tocTemplateText = fread($tocFileTemplate, filesize("EpubTemplate/OEBPS/toc.ncx"));
		fclose($tocFileTemplate);

		$output = str_replace("postlinks", $ncxlinks, $tocTemplateText);

		$outputfile = $this->directory."/OEBPS/toc.ncx";
		$outputfh = fopen($outputfile, "w");
		fwrite($outputfh, $output);
		fclose($outputfh);


	}

	function gen_zip(){
		$zip = new ZipArchive();
		$zip->open("$this->directory.zip", ZipArchive::CREATE);
		$zip->addfile("$this->directory/mimetype","mimetype");

		$zip->close();
		$zip->open("$this->directory.zip");

		$dirs = array("OEBPS",
					  "OEBPS/Images",
					  "OEBPS/Styles",
					  "OEBPS/Text",
					  "META-INF"
					);
		foreach ($dirs as $value) {
			$zip->addEmptyDir($value);
		}
		
		$zip->addfile("$this->directory/META-INF/container.xml","META-INF/container.xml");
		$zip->addfile("$this->directory/OEBPS/Images/cover.jpg","OEBPS/Images/cover.jpg");
		$zip->addfile("$this->directory/OEBPS/Images/backcover.jpg","OEBPS/Images/backcover.jpg");
		$zip->addfile("$this->directory/OEBPS/Styles/stylesheet.css","OEBPS/Styles/stylesheet.css");

		$zip->addfile("$this->directory/OEBPS/Text/bookcover.xhtml","OEBPS/Text/bookcover.xhtml");
		$zip->addfile("$this->directory/OEBPS/Text/backcover.xhtml","OEBPS/Text/backcover.xhtml");
		$zip->addfile("$this->directory/OEBPS/Text/title.xhtml","OEBPS/Text/title.xhtml");
		$zip->addfile("$this->directory/OEBPS/Text/toc.xhtml","OEBPS/Text/toc.xhtml");

		$posts = $this->collection->posts;
		foreach ($posts as $value) {
			$posttitle = $value["title"];
			$postauthor = $value["author"];
			$zip->addfile("$this->directory/OEBPS/Text/$posttitle--$postauthor.xhtml","OEBPS/Text/$posttitle--$postauthor.xhtml");
		}

		$zip->addfile("$this->directory/OEBPS/content.opf","OEBPS/content.opf");
		$zip->addfile("$this->directory/OEBPS/toc.ncx","OEBPS/toc.ncx");

		$zip->close();
	}

	function gen_epub(){
		$this->createbookdir();
		$this->gen_title_file();
		$this->gen_text_files();
		$this->gen_table_contents();
		$this->gen_zip();
		rename("$this->directory.zip", "$this->directory.epub");
	}

}


 ?>